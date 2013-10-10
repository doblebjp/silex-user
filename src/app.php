<?php

require __DIR__ . '/../vendor/autoload.php';

use Silex\Application;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;

$app = new Application();

$app['orm.em'] = $app->share(function () use ($app) {
    $paths = [__DIR__ . '/../src'];
    $isDevMode = true;

    $dbParams = [
        'driver'   => 'pdo_sqlite',
        'path'     => __DIR__ . '/../data/app.db',
    ];

    $config = Setup::createAnnotationMetadataConfiguration($paths, $isDevMode);
    $entityManager = EntityManager::create($dbParams, $config);

    return $entityManager;
});

return $app;
