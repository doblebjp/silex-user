<?php

use SilexUser\Form\UserType;
use SilexUser\User;
use Symfony\Component\Form\Forms;
use Symfony\Component\Form\FormBuilder;
use Symfony\Component\Form\Extension\Validator\Type\FormTypeValidatorExtension;
use Symfony\Component\Form\Test\TypeTestCase;

class UserTypeTest extends TypeTestCase
{
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
    }

    public function testUsingUsername()
    {
        $form = $this->factory->create(new UserType(false));
        $this->assertTrue($form->has('username'));
    }

    public function testEmailAsIdentity()
    {
        $form = $this->factory->create(new UserType(true));
        $form->submit([
            'email' => 'test@example.com'
        ]);

        $this->assertFalse($form->has('username'));

        $user = $form->getData();
        $this->assertEquals('test@example.com', $user->getUsername());
    }

    public function testPasswordFieldNotMapped()
    {
        $form = $this->factory->create(new UserType(true));
        $this->assertTrue($form->has('password'));
        $this->assertFalse($form->get('password')->getConfig()->getMapped());
    }
}
