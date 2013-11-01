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

    public function __construct($emailAsIdentity, $encoderFactory)
    {
        $this->emailAsIdentity = (boolean) $emailAsIdentity;
        $this->encoderFactory = $encoderFactory;
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
            ->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) {
                    $user = $event->getForm()->getData();
                    $password = $event->getForm()->get('password')->getData();
                    $user->randomSalt();
                    $encoder = $this->encoderFactory->getEncoder($user);
                    $hash = $encoder->encodePassword($password, $user->getSalt());
                    $user->setPassword($hash);
                }
            )

        ;
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
