<?php

namespace SilexUser\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\OptionsResolver\Options;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class CredentialsType extends AbstractType
{
    protected $passwordEncoder;

    public function __construct(PasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        $builder->add('password', 'repeated', [
            'type' => 'password',
            'invalid_message' => 'The password fields must match.',
            'first_options'  => ['label' => 'Password'],
            'second_options' => ['label' => 'Retype'],
        ]);

        // encode password
        $builder->addEventSubscriber(new EncodePasswordSubscriber($this->passwordEncoder));
    }

    public function setDefaultOptions(OptionsResolverInterface $resolver)
    {
        $resolver->setDefaults([
            'data_class' => 'SilexUser\User',
            'validation_groups' => ['Credentials'],
        ]);
    }

    public function getName()
    {
        return 'credentials';
    }
}
