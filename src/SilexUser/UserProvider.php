<?php

namespace SilexUser;

use Symfony\Component\Security\Core\User\UserProviderInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\Exception\UnsupportedUserException;
use Symfony\Component\Security\Core\Exception\UsernameNotFoundException;
use Doctrine\ORM\EntityManager;

class UserProvider implements UserProviderInterface
{
    protected $em;
    protected $userClass;

    public function __construct(EntityManager $em, $userClass)
    {
        $this->em = $em;
        $this->userClass = $userClass;
    }

    public function loadUserByUsername($username)
    {
        $user = $this->em->getRepository($this->userClass)->findOneByUsername($username);

        if (null === $user) {
            throw new UsernameNotFoundException(sprintf('Username "%s" does not exist.', $username));
        }

        return $user;
    }

    public function refreshUser(UserInterface $user)
    {
        if (!$user instanceof User) {
            throw new UnsupportedUserException(sprintf('Instances of "%s" are not supported.', get_class($user)));
        }

        return $this->loadUserByUsername($user->getUsername());
    }

    public function supportsClass($class)
    {
        return $class === $this->userClass;
    }
}
