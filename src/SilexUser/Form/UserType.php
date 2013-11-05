<?php

namespace SilexUser\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

class UserType extends AbstractType
{
    protected $encoderFactory;

    public function __construct($encoderFactory)
    {
        $this->encoderFactory = $encoderFactory;
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
            'second_options' => ['label' => 'Repeat Password'],
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
        $builder->addEventListener(
            FormEvents::POST_SUBMIT,
            function (FormEvent $event) {
                $user = $event->getData();
                $user->randomSalt();
                $encoder = $this->encoderFactory->getEncoder($user);
                $hash = $encoder->encodePassword($user->getPassword(), $user->getSalt());
                $user->setPassword($hash);
            }
        );
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'SilexUser\User',
            'email_as_identity' => false,
            'validation_groups' => function (Options $options) {
                return $options['email_as_identity'] ? 'RegisterEmail' : 'RegisterUsername';
            },
        ]);
    }

    public function getName()
    {
        return 'user';
    }
}
