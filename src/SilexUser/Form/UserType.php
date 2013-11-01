<?php

namespace SilexUser\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;

class UserType extends AbstractType
{
    protected $emailAsIdentity;
    protected $encoderFactory;
    protected $roles;

    public function __construct($emailAsIdentity, $encoderFactory, array $roles)
    {
        $this->emailAsIdentity = (boolean) $emailAsIdentity;
        $this->encoderFactory = $encoderFactory;
        $this->roles = $roles;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($this->emailAsIdentity) {
            $builder->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) {
                    $user = $event->getForm()->getData();
                    $user->setUsername($user->getEmail());
                }
            );
        } else {
            $builder->add('username');
        }


        $builder
            ->add('email', 'email', [
                'constraints' => [
                    new Assert\NotBlank(),
                    new Assert\Email(),
                ],
                'attr' => ['autocomplete' => 'off'],
            ])
            ->add('password', 'repeated', [
                'type' => 'password',
                'mapped' => false,
                'invalid_message' => 'The password fields must match.',
                'first_options'  => ['label' => 'Password'],
                'second_options' => ['label' => 'Repeat Password'],
            ])
        ;

        // taking care of defaults
        $roles = $this->roles;

        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) use ($roles) {
                $user = $event->getForm()->getData();
                $password = $event->getForm()->get('password')->getData();
                if (null !== $password) {
                    $user->randomSalt();
                    $encoder = $this->encoderFactory->getEncoder($user);
                    $hash = $encoder->encodePassword($password, $user->getSalt());
                    $user->setPassword($hash);
                }

                foreach ($roles as $role) {
                    if (!$user->getAssignedRoles()->contains($role)) {
                        $user->addAssignedRole($role);
                    }
                }
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'SilexUser\User',
            'validation_groups' => false,
        ]);
    }

    public function getName()
    {
        return 'user';
    }
}
