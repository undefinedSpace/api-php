<?php
define('APPPATH', __DIR__ . '/app/');
require __DIR__ . '/vendor/autoload.php';
$database = \DrMVC\Core\Config::load('database');

return [
    'paths' => [
        'migrations' => 'migrations'
    ],
    'migration_base_class' => '\Undefined\Migrations\Migration',
    'environments' => [
        'default_migration_table' => 'phinxlog',
        'default_database' => 'production',
        'production' => [
            'adapter' => $database['driver'],
            'host' => $database['host'],
            'port' => $database['port'],
            'name' => $database['database'],
            'user' => $database['username'],
            'pass' => $database['password']
        ]
    ]
];
