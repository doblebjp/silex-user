<?php

$app = require __DIR__ . '/../src/app.php';

$app['silex_user.entity_manager'] = $app->share(function () use ($app) {
    // return preconfigured entity manager
    return $app['orm.em'];
});

return $app;
