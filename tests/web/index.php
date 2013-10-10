<?php

require __DIR__ . '/../../vendor/autoload.php';

$app = require __DIR__ . '/../app.php';

use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;

// services for web
$app->register(new TwigServiceProvider(), ['twig.path' => __DIR__ . '/../../templates']);
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new ValidatorServiceProvider());
$app->register(new ServiceControllerServiceProvider());

// controllers
$app->get('/', function () use ($app) {
    return "Silex user web test";
});

$app->run();
