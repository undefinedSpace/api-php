<?php namespace Undefined;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Undefined\Models\Projects as Model_Projects;

class Projects
{
    public $id_project;
    public $id_server;

    public function __construct()
    {
        $this->_projects = new Model_Projects();
    }

    public function __invoke(Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        if (!empty($id)) $data = $this->_projects->getByID($id);
        else $data = $this->_projects->getAll();

        return $response->getBody()->write(json_encode($data));
    }

}
