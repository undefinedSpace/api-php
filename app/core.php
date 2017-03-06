<?php
use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

$app = new \Slim\App();

$app->group('/api', function () {

    $this->group('/v1', function () {

        $this->post('/sync', \Undefined\Sync::class . ':post');
        $this->put('/sync', \Undefined\Sync::class . ':put');

        $this->get('/changes/{id_item:[0-9]+}', function (Request $request, Response $response) {
            $id_item = $request->getAttribute('id_item');
            $this->_changes = new \Undefined\Models\Changes();

            if (!empty($id_item)) $data = $this->_changes->getChangesByID($id_item);
            else $data = array();

            return $response->getBody()->write(json_encode($data));
        });

        $this->get('/accords/{id_project:[0-9]+}[/{id_type:[0-9]+}]', function (Request $request, Response $response) {
            $id_project = $request->getAttribute('id_project');
            $id_type = $request->getAttribute('id_type');
            $this->_accords = new \Undefined\Models\Accords();

            $data = $this->_accords->getItemsByProject($id_project, $id_type);

            return $response->getBody()->write(json_encode($data));
        });

        $this->get('/projects[/{id:[0-9]+}]', function (Request $request, Response $response) {
            $id = $request->getAttribute('id');
            $this->_projects = new \Undefined\Models\Projects();

            if (!empty($id)) $data = $this->_projects->getByID($id);
            else $data = $this->_projects->getAll();

            return $response->getBody()->write(json_encode($data));
        });

        $this->get('/servers/ip/{ip}', function (Request $request, Response $response) {
            $id = $request->getAttribute('ip');
            $this->_servers = new \Undefined\Models\Servers();

            if (!empty($id)) $data = $this->_servers->getByIP($id);
            else $data = array();

            return $response->getBody()->write(json_encode($data));
        });

        $this->get('/servers[/{id:[0-9]+}]', function (Request $request, Response $response) {
            $id = $request->getAttribute('id');
            $this->_servers = new \Undefined\Models\Servers();

            if (!empty($id)) $data = $this->_servers->getByID($id);
            else $data = $this->_servers->getAll();

            return $response->getBody()->write(json_encode($data));
        });

        $this->get('/events[/{id:[0-9]+}]', function (Request $request, Response $response) {
            $id = $request->getAttribute('id');
            $this->_events = new \Undefined\Models\Events();

            if (!empty($id)) $data = $this->_events->getByID($id);
            else $data = $this->_events->getAll();

            return $response->getBody()->write(json_encode($data));
        });

        $this->get('/items[/{id:[0-9]+}]', function (Request $request, Response $response) {
            $id = $request->getAttribute('id');
            $this->_items = new \Undefined\Models\Items();

            if (!empty($id)) $data = $this->_items->getByID($id);
            else $data = $this->_items->getAll();

            return $response->getBody()->write(json_encode($data));
        });

        $this->get('/items/inode/{inode:[0-9]+}/{id_server:[0-9]+}', function (Request $request, Response $response) {
            $inode = $request->getAttribute('inode');
            $id_server = $request->getAttribute('id_server');
            $this->_items = new \Undefined\Models\Items();

            if (!empty($id)) $data = $this->_items->getByInode($id_server,$inode);
            else $data = array();

            return $response->getBody()->write(json_encode($data));
        });

    });
});
