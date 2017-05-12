<?php namespace Undefined;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Undefined\Models\Events as Model_Events;

class Events
{
    public $id_project;
    public $id_server;

    public function __construct()
    {
        $this->_events = new Model_Events();
    }

    public function __invoke(Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        if (!empty($id)) $data = $this->_events->getByID($id);
        else $data = $this->_events->getAll();

        return $response->getBody()->write(json_encode($data));
    }

}
