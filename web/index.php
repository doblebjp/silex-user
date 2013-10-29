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
    return 'Silex user web test';
});

$app->get('/admin/test', function () use ($app) {
    return 'Allowed for admin';
});

$app->get('/user/test', function () use ($app) {
    return 'Allowed for admin and user';
});

$app->run();
