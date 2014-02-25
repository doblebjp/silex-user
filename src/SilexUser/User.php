<?php

namespace SilexUser;

use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Mapping\ClassMetadata;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @Entity
 */
class User implements UserInterface
{
    /**
     * @Id @Column(type="integer") @GeneratedValue
     */
    protected $id;

    /**
     * @Column(length=50, unique=true)
     */
    protected $username;

    /**
     * @Column
     */
    protected $password;

    /**
     * @Column(nullable=true)
     */
    protected $email;

    /**
     * @Column
     */
    protected $salt;

    /**
     * @ManyToMany(targetEntity="Role", inversedBy="users")
     */
    protected $assignedRoles;

    /**
     * @Column(length=64, nullable=true)
     */
    protected $resetToken;

    static public function loadValidatorMetadata(ClassMetadata $metadata)
    {
        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'username',
            'message' => 'Username is already taken.',
            'groups' => ['RegisterUsername']
        ]));

        $metadata->addPropertyConstraint('username', new Assert\NotBlank([
            'groups' => ['Default', 'RegisterUsername', 'RegisterEmail']
        ]));

        $metadata->addPropertyConstraint('username', new Assert\Length([
            'min' => 8,
            'groups' => ['RegisterUsername']
        ]));

        $metadata->addPropertyConstraint('username', new Assert\Email([
            'groups' => ['RegisterEmail']
        ]));

        $metadata->addPropertyConstraint('password', new Assert\NotBlank([
            'groups' => ['Default', 'Credentials']
        ]));

        $metadata->addPropertyConstraint('password', new Assert\Length([
            'min' => 8,
            'max' => 4096,
            'groups' => ['Credentials']
        ]));

        $metadata->addPropertyConstraint('email', new Assert\Email([
            'groups' => ['Default', 'RegisterUsername', 'RegisterEmail']
        ]));

        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'email',
            'message' => 'This email address is already used.',
            'groups' => ['RegisterUsername'],
        ]));

        $metadata->addConstraint(new UniqueEntity([
            'fields' => 'username',
            'message' => 'This email address is already used.',
            'groups' => ['RegisterEmail'],
        ]));
    }

    /**
     * Get id
     *
     * @return integer
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Set username
     *
     * @param string $username
     * @return User
     */
    public function setUsername($username)
    {
        $this->username = $username;

        return $this;
    }

    /**
     * Get username
     *
     * @return string
     */
    public function getUsername()
    {
        return $this->username;
    }

    /**
     * Set password
     *
     * @param string $password
     * @return User
     */
    public function setPassword($password)
    {
        $this->password = $password;

        return $this;
    }

    /**
     * Get password
     *
     * @return string
     */
    public function getPassword()
    {
        return $this->password;
    }

    /**
     * Set salt
     *
     * @param string $salt
     * @return User
     */
    public function setSalt($salt)
    {
        $this->salt = $salt;

        return $this;
    }

    /**
     * Get salt
     *
     * @return string
     */
    public function getSalt()
    {
        return $this->salt;
    }

    /**
     * Constructor
     */
    public function __construct()
    {
        $this->assignedRoles = new \Doctrine\Common\Collections\ArrayCollection();
    }

    /**
     * Get roles
     *
     * @return array
     */
    public function getRoles()
    {
        return $this->assignedRoles->map(function (Role $role) {
            return $role->getRole();
        })->toArray();
    }

    /**
     * Not implemented
     */
    public function eraseCredentials()
    {

    }

    /**
     * Add assignedRoles
     *
     * @param \SilexUser\Role $assignedRoles
     * @return User
     */
    public function addAssignedRole(\SilexUser\Role $assignedRoles)
    {
        $this->assignedRoles[] = $assignedRoles;

        return $this;
    }

    /**
     * Remove assignedRoles
     *
     * @param \SilexUser\Role $assignedRoles
     */
    public function removeAssignedRole(\SilexUser\Role $assignedRoles)
    {
        $this->assignedRoles->removeElement($assignedRoles);
    }

    /**
     * Get assignedRoles
     *
     * @return \Doctrine\Common\Collections\Collection
     */
    public function getAssignedRoles()
    {
        return $this->assignedRoles;
    }

    /**
     * Set email
     *
     * @param string $email
     * @return User
     */
    public function setEmail($email)
    {
        $this->email = $email;

        return $this;
    }

    /**
     * Get email
     *
     * @return string
     */
    public function getEmail()
    {
        return $this->email;
    }

    public function randomSalt($length = 8)
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomString .= $characters[mt_rand(0, strlen($characters) - 1)];
        }
        $this->salt = $randomString;

        return $this;
    }

    /**
     * Set resetToken
     *
     * @param string $resetToken
     * @return User
     */
    public function setResetToken($resetToken)
    {
        $this->resetToken = $resetToken;

        return $this;
    }

    /**
     * Get resetToken
     *
     * @return string 
     */
    public function getResetToken()
    {
        return $this->resetToken;
    }
}
