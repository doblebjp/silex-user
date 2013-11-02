<?php

require __DIR__ . '/../vendor/autoload.php';

use Silex\Application;
use Silex\Provider\SecurityServiceProvider;
use Silex\Provider\TwigServiceProvider;
use Silex\Provider\UrlGeneratorServiceProvider;
use Silex\Provider\SessionServiceProvider;
use Silex\Provider\FormServiceProvider;
use Silex\Provider\ValidatorServiceProvider;
use Silex\Provider\TranslationServiceProvider;
use Silex\Provider\ServiceControllerServiceProvider;
use Doctrine\ORM\Tools\Setup;
use Doctrine\ORM\EntityManager;
use Dominikzogg\Silex\Provider\DoctrineOrmManagerRegistryProvider;

$app = new Application();

// configure entity manager
$app['orm.em'] = $app->share(function () use ($app) {
    $paths = [__DIR__ . '/../src/mappings'];
    $proxyDir = __DIR__ . '/../data';
    $isDevMode = true;

    $dbParams = [
        'driver' => 'pdo_sqlite',
        'path'   => __DIR__ . '/../data/app.db',
    ];

    $config = Setup::createYamlMetadataConfiguration($paths, $isDevMode, $proxyDir);
    $entityManager = EntityManager::create($dbParams, $config);

    return $entityManager;
});

$app['orm.ems'] = $app->share(function () use ($app) {
    return ['default' => $app['orm.em']];
});

$app['orm.ems.default'] = 'default';

// dependency services
$app->register(new TwigServiceProvider());
$app->register(new UrlGeneratorServiceProvider());
$app->register(new SessionServiceProvider());
$app->register(new ServiceControllerServiceProvider());
$app->register(new SecurityServiceProvider());
$app->register(new FormServiceProvider());
$app->register(new TranslationServiceProvider(), [
    'locale_fallbacks' => ['en'],
]);
$app->register(new DoctrineOrmManagerRegistryProvider());
$app['form.extensions'] = $app->share($app->extend('form.extensions', function ($extensions, $app) {
    $extensions[] = new Symfony\Bridge\Doctrine\Form\DoctrineOrmExtension($app['doctrine']);

    return $extensions;
}));

$app->register(new ValidatorServiceProvider(), [
    'validator.validator_service_ids' => [
        'doctrine.orm.validator.unique' => 'silex_user.unique_entity_validator'
    ]
]);

// silex user service
$app->register(new SilexUser\UserServiceProvider(), [
    'silex_user.entity_manager_key' => 'orm.em',
    'silex_user.login.default_target_path' => 'user_test',
]);

return $app;
