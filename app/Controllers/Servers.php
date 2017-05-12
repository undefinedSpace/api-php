<?php namespace Undefined;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Undefined\Models\Servers as Model_Servers;

class Servers
{
    public $id_project;
    public $id_server;

    public function __construct()
    {
        $this->_servers = new Model_Servers();
    }

    public function ip(Request $request, Response $response)
    {
        $id = $request->getAttribute('ip');

        if (!empty($id)) $data = $this->_servers->getByIP($id);
        else $data = array();

        return $response->getBody()->write(json_encode($data));
    }

    public function show(Request $request, Response $response)
    {
        $id = $request->getAttribute('id');
        $this->_servers = new \Undefined\Models\Servers();

        if (!empty($id)) $data = $this->_servers->getByID($id);
        else $data = $this->_servers->getAll();

        return $response->getBody()->write(json_encode($data));
    }

}
