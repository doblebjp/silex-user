<?php

require __DIR__ . '/../vendor/autoload.php';

$app = require __DIR__ . '/../src/app.php';

$app['debug'] = true;
$app['security.access_rules'] = [
    ['^/admin', 'ROLE_ADMIN'],
    ['^/user', 'ROLE_USER'],
];

// controllers
$app->get('/', function () use ($app) {
    return $app['twig']->render('@SilexUser/test.html.twig');
})->bind('home');

$app->get('/admin/test', function () use ($app) {
    return $app['twig']->render('@SilexUser/test.html.twig', ['title' => 'Admin Only']);
})->bind('admin_test');

$app->get('/user/test', function () use ($app) {
    return $app['twig']->render('@SilexUser/test.html.twig', ['title' => 'Admin and User']);
})->bind('user_test');

$app->run();
