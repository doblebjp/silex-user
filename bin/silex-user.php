<?php

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Application;
use Doctrine\DBAL\Tools\Console\Helper\ConnectionHelper;
use Doctrine\ORM\Tools\Console\Helper\EntityManagerHelper;
use Symfony\Component\Console\Helper\DialogHelper;

$app = require __DIR__ . '/../src/app.php';

$helperSet = new HelperSet([
    'em' => new EntityManagerHelper($app['orm.em']),
    'dialog' => new DialogHelper,
]);

$cli = new Application('SilexUser Command Line Interface', '1.0');
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);
$cli->addCommands([
    new SilexUser\Console\DefaultRolesCommand(),
    new SilexUser\Console\CreateUserCommand(),
]);
$cli->run();
