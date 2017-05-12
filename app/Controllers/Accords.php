<?php namespace Undefined;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Undefined\Models\Accords as Model_Accords;

class Accords
{
    public $id_project;
    public $id_server;

    public function __construct()
    {
        $this->_accords = new Model_Accords();
    }

    public function __invoke(Request $request, Response $response)
    {
        $id_project = $request->getAttribute('id_project');
        $id_type = $request->getAttribute('id_type');

        $data = $this->_accords->getItemsByProject($id_project, $id_type);

        return $response->getBody()->write(json_encode($data));
    }

}
