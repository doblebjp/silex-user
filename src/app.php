<?php

require __DIR__ . '/../vendor/autoload.php';

use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$app = new Application();

// configure entity manager
$app['orm.em'] = $app->share(function () use ($app) {
    $paths = [__DIR__ . '/../src'];
    $proxyDir = __DIR__ . '/../data';
    $isDevMode = true;

    $dbParams = [
        'driver' => 'pdo_sqlite',
        'path'   => __DIR__ . '/../data/app.db',
    ];

    $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode, $proxyDir);
    $entityManager = EntityManager::create($dbParams, $config);

    return $entityManager;
});

// dependency services
$app->register(new TwigServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new SecurityServiceProvider());

// silex user service
$app->register(new SilexUser\UserServiceProvider(), [
    'silex_user.entity_manager_key' => 'orm.em',
]);

return $app;
