<?php namespace Undefined;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Undefined\Models\Changes as Model_Changes;

class Changes
{
    public $id_project;
    public $id_server;

    public function __construct()
    {
        $this->_changes = new Model_Changes();
    }

    public function __invoke(Request $request, Response $response)
    {
        $id_item = $request->getAttribute('id_item');

        if (!empty($id_item)) $data = $this->_changes->getChangesByID($id_item);
        else $data = array();

        return $response->getBody()->write(json_encode($data));
    }

}
