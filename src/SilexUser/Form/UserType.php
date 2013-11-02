<?php

namespace SilexUser\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

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

        if ($this->emailAsIdentity) {
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
        $groups = ['Default'];
        if ($this->emailAsIdentity) {
            $groups[] = 'RegisterEmail_CheckFirst';
            $groups[] = 'RegisterEmail';
        } else {
            $groups[] = 'RegisterUsername';
        }

        $resolver->setDefaults([
            'data_class' => 'SilexUser\User',
            'validation_groups' => $groups,
        ]);
    }

    public function getName()
    {
        return 'user';
    }
}
