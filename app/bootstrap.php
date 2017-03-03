<?php

/**
 * Application models
 */

include __DIR__ . '/../app/models/Accords.php';
include __DIR__ . '/../app/models/Events.php';
include __DIR__ . '/../app/models/Changes.php';
include __DIR__ . '/../app/models/Items.php';
include __DIR__ . '/../app/models/Projects.php';
include __DIR__ . '/../app/models/Servers.php';

/**
 * Application controllers
 */

include __DIR__ . '/../app/controllers/Sync.php';

/**
 * Database configurations
 */

// Database information
$settings = array(
    'driver' => 'mysql',
    'host' => '127.0.0.1',
    'database' => 'undefinedSpace',
    'username' => 'us',
    'password' => 'us_pass',
    'collation' => 'utf8_general_ci',
    'prefix' => ''
);

// Bootstrap Eloquent ORM
$container = new Illuminate\Container\Container;
$connFactory = new \Illuminate\Database\Connectors\ConnectionFactory($container);
$conn = $connFactory->make($settings);
$resolver = new \Illuminate\Database\ConnectionResolver();
$resolver->addConnection('default', $conn);
$resolver->setDefaultConnection('default');
\Illuminate\Database\Eloquent\Model::setConnectionResolver($resolver);
