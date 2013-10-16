<?php

namespace SilexUser;

use Symfony\Component\Security\Core\User\UserInterface;

class User implements UserInterface
{
    protected $id;
    protected $username;
    protected $password;
    protected $salt;
    protected $assignedRoles;

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
}
