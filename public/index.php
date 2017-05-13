<?php
// Composer autoload
include __DIR__ . '/../vendor/autoload.php';

// Project root
define('APPPATH', __DIR__ . '/../app/');

$app = new \Slim\App();

// Database support
include __DIR__ . '/../app/database.php';

// Enable routes
include __DIR__ . '/../app/routes.php';

$app->run();
