<?php
$app->group('/api', function () {

    $this->group('/v1', function () {

        // Sync the file system
        $this->post('/sync', \Undefined\Sync::class . ':post');
        $this->put('/sync', \Undefined\Sync::class . ':put');

        // Other methods
        $this->get('/changes/{id_item:[0-9]+}', \Undefined\Changes::class);
        $this->get('/accords/{id_project:[0-9]+}[/{id_type:[0-9]+}]', \Undefined\Accords::class);
        $this->get('/projects[/{id:[0-9]+}]', \Undefined\Projects::class);
        $this->get('/events[/{id:[0-9]+}]', \Undefined\Events::class);

        // Work with servers list
        $this->get('/servers/ip/{ip}', \Undefined\Servers::class . ':ip');
        $this->get('/servers[/{id:[0-9]+}]', \Undefined\Servers::class . ':show');

        // Items of files systems
        $this->get('/items[/{id:[0-9]+}]', \Undefined\Items::class . ':get');
        $this->get('/items/inode/{inode:[0-9]+}/{id_server:[0-9]+}', \Undefined\Items::class . ':inode');

    });

});
