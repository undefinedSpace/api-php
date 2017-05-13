<?php
$app->group('/api', function () {

    $this->group('/v1', function () {

        // Sync the file system
        $this->post('/sync', \Undefined\Controllers\Sync::class . ':post');
        $this->put('/sync', \Undefined\Controllers\Sync::class . ':put');

        // Other methods
        $this->get('/changes/{id_item:[0-9]+}', \Undefined\Controllers\Changes::class);
        $this->get('/accords/{id_project:[0-9]+}[/{id_type:[0-9]+}]', \Undefined\Controllers\Accords::class);
        $this->get('/projects[/{id:[0-9]+}]', \Undefined\Controllers\Projects::class);
        $this->get('/events[/{id:[0-9]+}]', \Undefined\Controllers\Events::class);

        // Work with servers list
        $this->get('/servers/ip/{ip}', \Undefined\Controllers\Servers::class . ':ip');
        $this->get('/servers[/{id:[0-9]+}]', \Undefined\Controllers\Servers::class . ':show');

        // Items of files systems
        $this->get('/items[/{id:[0-9]+}]', \Undefined\Controllers\Items::class . ':get');
        $this->get('/items/inode/{inode:[0-9]+}/{id_server:[0-9]+}', \Undefined\Controllers\Items::class . ':inode');

    });

});
