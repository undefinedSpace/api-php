<?php
include __DIR__ . '/../vendor/autoload.php';
include __DIR__ . '/../app/bootstrap.php';
include __DIR__ . '/../app/core.php';

// All available routes
//$routes = $app->getContainer()->get('router')->getRoutes();
//print_r($router);
//die();

$app->run();
