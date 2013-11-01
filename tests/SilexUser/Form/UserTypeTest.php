<?php

use SilexUser\Form\UserType;
use SilexUser\User;
use SilexUser\Role;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;
use Symfony\Component\Security\Core\Encoder\EncoderFactory;
use Symfony\Component\Security\Core\Encoder\MessageDigestPasswordEncoder;

class UserTypeTest extends TypeTestCase
{
    protected $encoderFactory;

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

        $this->encoderFactory = new EncoderFactory([
            'Symfony\Component\Security\Core\User\UserInterface' => new MessageDigestPasswordEncoder()
        ]);
    }

    public function testUsingUsername()
    {
        $form = $this->factory->create(new UserType(false, $this->encoderFactory, [new Role()]));
        $this->assertTrue($form->has('username'));
    }

    public function testEmailAsIdentity()
    {
        $form = $this->factory->create(new UserType(true, $this->encoderFactory, [new Role()]));
        $form->submit([
            'email' => 'test@example.com'
        ]);

        $this->assertFalse($form->has('username'));

        $user = $form->getData();
        $this->assertEquals('test@example.com', $user->getUsername());
    }

    public function testPasswordFieldNotMapped()
    {
        $form = $this->factory->create(new UserType(true, $this->encoderFactory, [new Role()]));
        $this->assertTrue($form->has('password'));
        $this->assertFalse($form->get('password')->getConfig()->getMapped());
    }

    public function testPasswordHashIsGeneratedForUser()
    {
        $form = $this->factory->create(new UserType(true, $this->encoderFactory, [new Role()]));
        $form->submit(['password' => ['first' => 'test', 'second' => 'test']]);
        $user = $form->getData();
        $hash = $this->encoderFactory->getEncoder($user)->encodePassword('test', $user->getSalt());
        $this->assertEquals($hash, $user->getPassword());
    }

    public function testEmptyPassword()
    {
        $form = $this->factory->create(new UserType(true, $this->encoderFactory, [new Role()]));
        $form->submit([]);
        $user = $form->getData();
        $this->assertEmpty($user->getPassword());
    }

    public function testUserIsAssignedRole()
    {
        $form = $this->factory->create(new UserType(true, $this->encoderFactory, [$role = new Role('ROLE_TEST')]));
        $form->submit([]);
        $user = $form->getData();
        $this->assertEquals(1, $user->getAssignedRoles()->count());
        $this->assertEquals($role, $user->getAssignedRoles()->current());
    }
}
