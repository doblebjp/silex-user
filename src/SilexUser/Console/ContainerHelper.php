<?php

namespace SilexUser\Console;

use Pimple;
use Symfony\Component\Console\Helper\Helper;

class ContainerHelper extends Helper
{
    protected $container;

    public function __construct(Pimple $container)
    {
        $this->container = $container;
    }

    public function getName()
    {
        return 'container';
    }

    public function getContainer()
    {
        return $this->container;
    }
}
