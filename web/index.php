<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../src/app.php';

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;

// services for web
$app->register(new TwigServiceProvider(), ['twig.path' => __DIR__ . '/../templates']);
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());

// security firewalls
$app->register(new SecurityServiceProvider());
$app->register(new SilexUser\UserServiceProvider());

$app['security.firewalls'] = [
    'login' => [
        'pattern' => '^/login$',
    ],
    'secured' => [
        'pattern' => '^/secure/',
        'form'    => ['login_path' => '/login', 'check_path' => '/secure/login_check'],
        'logout'  => ['logout_path' => '/secure/logout'],
        'users'   => $app['silex_user.user_provider'],
    ],
    'unsecured' => ['anonymous' => true],
];

$app['security.access_rules'] = [
    ['^/secure/admin', 'ROLE_ADMIN'],
    ['^/secure/user', 'ROLE_USER'],
];

// controllers
$app->get('/', function () use ($app) {
    return 'Silex user web test';
});

$app->get('/login', 'silex_user.auth_controller:login');

$app->get('/secure/admin', function () use ($app) {
    return 'Allowed for admin';
});

$app->get('/secure/user', function () use ($app) {
    return 'Allowed for admin and user';
});

$app->run();
