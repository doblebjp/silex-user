<?php

use SilexUser\Form\UserType;
use SilexUser\User;
use SilexUser\Role;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class UserTypeTest extends TypeTestCase
{
    protected $passwordEncoder;

    protected function setUp()
    {
        parent::setUp();

        $this->factory = Forms::createFormFactoryBuilder()
            ->addExtensions($this->getExtensions())
            ->addTypeExtension(
                new FormTypeValidatorExtension(
                    $this->getMock('Symfony\Component\Validator\ValidatorInterface')
                )
            )
            ->addTypeGuesser(
                $this->getMockBuilder(
                    'Symfony\Component\Form\Extension\Validator\ValidatorTypeGuesser'
                )
                    ->disableOriginalConstructor()
                    ->getMock()
            )
            ->getFormFactory();

        $this->dispatcher = $this->getMock('Symfony\Component\EventDispatcher\EventDispatcherInterface');
        $this->builder = new FormBuilder(null, null, $this->dispatcher, $this->factory);

        $this->passwordEncoder = $this->getMock('Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder');
    }

    public function testUsingUsername()
    {
        $form = $this->factory->create(new UserType($this->passwordEncoder));
        $this->assertTrue($form->has('username'));
    }

    public function testEmailAsIdentity()
    {
        $form = $this->factory->create(new UserType($this->passwordEncoder));
        $form->submit([
            'username' => 'test@example.com'
        ]);

        $user = $form->getData();
        $this->assertEquals('test@example.com', $user->getUsername());
    }

    public function testPasswordHashIsGeneratedForUser()
    {
        $form = $this->factory->create(new UserType($this->passwordEncoder));
        $form->submit(['password' => ['first' => 'test', 'second' => 'test']]);
        $user = $form->getData();
        $hash = $this->passwordEncoder->encodePassword('test', $user->getSalt());
        $this->assertEquals($hash, $user->getPassword());
    }
}
