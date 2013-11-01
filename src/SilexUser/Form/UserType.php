<?php

namespace SilexUser\Form;

use Symfony\Component\Form\AbstractType;
use Symfony\Component\Form\FormBuilderInterface;
use Symfony\Component\OptionsResolver\OptionsResolverInterface;
use Symfony\Component\Validator\Constraints as Assert;

class UserType extends AbstractType
{
    protected $emailAsIdentity;

    public function __construct($emailAsIdentity)
    {
        $this->emailAsIdentity = (boolean) $emailAsIdentity;
    }

    public function buildForm(FormBuilderInterface $builder, array $options)
    {
        if (!$this->emailAsIdentity) {
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
