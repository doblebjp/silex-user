<?php

namespace SilexUser\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;
use SilexUser\Entity;

class UserType extends AbstractType
{
    protected $passwordEncoder;

    public function __construct(PasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if ($options['email_as_identity']) {
            $builder->add('username', 'text', [
                'attr' => ['autocomplete' => 'off'],
                'label' => 'Email',
            ]);
        } else {
            $builder->add('username', 'text', ['attr' => ['autocomplete' => 'off']]);
            $builder->add('email', 'text', [
                'attr' => ['autocomplete' => 'off'],
                'required' => false,
            ]);
        }

        $builder->add('password', 'repeated', [
            'type' => 'password',
            'invalid_message' => 'The password fields must match.',
            'first_options'  => ['label' => 'Password'],
            'second_options' => ['label' => 'Retype'],
        ]);

        if ($options['email_as_identity']) {
            $builder->addEventListener(
                FormEvents::POST_SUBMIT,
                function (FormEvent $event) {
                    $user = $event->getData();
                    $user->setEmail($user->getUsername());
                }
            );
        }

        // encode password
        $builder->addEventSubscriber(new EncodePasswordSubscriber($this->passwordEncoder));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => Entity::$user,
            'email_as_identity' => false,
            'validation_groups' => function (Options $options) {
                $groups[] = $options['email_as_identity'] ? 'RegisterEmail' : 'RegisterUsername';
                $groups[] = 'Credentials';

                return $groups;
            },
        ]);
    }

    public function getName()
    {
        return 'user';
    }
}
