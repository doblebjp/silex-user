<?php

use Symfony\Component\Validator\Validator;
use Symfony\Component\Validator\Mapping\ClassMetadataFactory;
use Symfony\Component\Validator\Mapping\Loader\StaticMethodLoader;
use Symfony\Component\Validator\DefaultTranslator;
use Symfony\Component\Validator\ConstraintValidatorFactory;
use SilexUser\User;

class UserTest extends PHPUnit_Framework_TestCase
{
    protected $validator;
    protected $constraintValidatorFactory;

    public function setUp()
    {
        $uniqueEntityValidatorStub
            = $this->getMockBuilder('Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntityValidator')
                ->disableOriginalConstructor()
                ->getMock();

        $uniqueEntityValidatorStub
            ->expects($this->any())
            ->method('validate')
            ->will($this->returnValue(null));

        $constraintValidatorFactory
            = $this->getMockBuilder('Symfony\Component\Validator\ConstraintValidatorFactory')
                ->setMethods(['getInstance'])
                ->getMock();

        $constraintValidatorFactory
            ->expects($this->any())
            ->method('getInstance')
            ->will($this->returnCallback(function ($constraint) use ($uniqueEntityValidatorStub) {
                $classname = $constraint->validatedBy();
                if ($classname === 'doctrine.orm.validator.unique') {
                    return $uniqueEntityValidatorStub;
                }

                return new $classname();
            }));

        $this->validator = new Validator(
            new ClassMetadataFactory(new StaticMethodLoader()),
            $constraintValidatorFactory,
            new DefaultTranslator()
        );
    }

    public function testEmptyUserIsNotValid()
    {
        $user = new User();
        $errors = $this->validator->validate($user);
        $this->assertGreaterThan(0, count($errors));
    }

    public function testMinimumValidUser()
    {
        $user = new User();
        $user->setUsername('testuser');
        $user->setPassword(md5('something'));
        $errors = $this->validator->validate($user);
        $this->assertEquals(0, count($errors));
    }

    public function testUsernameCannotBeBlank()
    {
        $user = new User();
        $errors = $this->validator->validate($user);
        $this->assertEquals('username', $errors[0]->getPropertyPath());
        $this->assertTrue((boolean) preg_match('/value should not be blank/', $errors[0]->getMessage()));
    }

    public function testUsernameLengthMinimum8RegisterUsername()
    {
        $user = new User();
        $user->setUsername('test');
        $errors = $this->validator->validate($user, ['RegisterUsername']);
        $this->assertEquals('username', $errors[0]->getPropertyPath());
        $this->assertTrue((boolean) preg_match('/too short/', $errors[0]->getMessage()));
    }

    public function testPasswordCannotBeBlank()
    {
        $user = new User();
        $user->setUsername('validusername');
        $errors = $this->validator->validate($user);
        $this->assertEquals('password', $errors[0]->getPropertyPath());
        $this->assertTrue((boolean) preg_match('/should not be blank/', $errors[0]->getMessage()));
    }

    public function testEmailMustBeValidFormat()
    {
        $user = new User();
        $user->setUsername('validusername');
        $user->setPassword('somepasswordhash');
        $user->setEmail('invalidaddress');
        $errors = $this->validator->validate($user);
        $this->assertEquals('email', $errors[0]->getPropertyPath());
        $this->assertTrue((boolean) preg_match('/not a valid email/', $errors[0]->getMessage()));

        $user->setEmail('test@example.net');
        $errors = $this->validator->validate($user);
        $this->assertEquals(0, count($errors));
    }
}
