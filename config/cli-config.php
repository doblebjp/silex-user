<?php
/**
 * Doctrine console configuration
 */

use Doctrine\ORM\Tools\Console\ConsoleRunner;

$app = require __DIR__ . '/../src/app.php';

return ConsoleRunner::createHelperSet($app['orm.em']);
