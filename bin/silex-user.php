<?php

use Symfony\Component\Console\Helper\HelperSet;
use Symfony\Component\Console\Helper\DialogHelper;
use Knp\Provider\ConsoleServiceProvider;

$app = require getcwd() . '/config/silex-user-config.php';

$app->register(new ConsoleServiceProvider(), [
    'console.name'              => 'SilexUser Command Line Interface',
    'console.version'           => '1.0',
    'console.project_directory' => getcwd(),
]);

$helperSet = new HelperSet([
    'dialog' => new DialogHelper(),
]);

$cli = $app['console'];
$cli->setCatchExceptions(true);
$cli->setHelperSet($helperSet);
$cli->addCommands([
    new SilexUser\Console\DefaultRolesCommand(),
    new SilexUser\Console\CreateUserCommand(),
]);
$cli->run();
