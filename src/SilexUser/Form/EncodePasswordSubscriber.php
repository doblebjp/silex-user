<?php

namespace SilexUser\Form;

use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Security\Core\Encoder\PasswordEncoderInterface;

class EncodePasswordSubscriber implements EventSubscriberInterface
{
    protected $passwordEncoder;

    public function __construct(PasswordEncoderInterface $passwordEncoder)
    {
        $this->passwordEncoder = $passwordEncoder;
    }

    public static function getSubscribedEvents()
    {
        return [FormEvents::POST_SUBMIT => 'postSubmit'];
    }

    public function postSubmit(FormEvent $event)
    {
        $user = $event->getData();
        $user->randomSalt();
        $hash = $this->passwordEncoder->encodePassword($user->getPassword(), $user->getSalt());
        $user->setPassword($hash);
    }
}
