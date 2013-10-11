<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../src/app.php';

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;

// services for web
$app->register(new TwigServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());

// security firewalls
$app->register(new SilexUser\UserServiceProvider());

// controllers
$app->get('/', function () use ($app) {
    return 'Silex user web test';
});

$app->get('/admin/test', function () use ($app) {
    return 'Allowed for admin';
});

$app->get('/user/test', function () use ($app) {
    return 'Allowed for admin and user';
});

$app->run();
