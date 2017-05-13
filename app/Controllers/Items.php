<?php namespace Undefined\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Undefined\Models\Items as Model_Items;

class Items
{
    public $id_project;
    public $id_server;

    public function __construct()
    {
        $this->_items = new Model_Items();
    }

    public function get(Request $request, Response $response)
    {
        $id = $request->getAttribute('id');

        if (!empty($id)) $data = $this->_items->getByID($id);
        else $data = $this->_items->getAll();

        return $response->getBody()->write(json_encode($data));
    }

    public function inode(Request $request, Response $response)
    {
        $inode = $request->getAttribute('inode');
        $id_server = $request->getAttribute('id_server');

        if (!empty($id)) $data = $this->_items->getByInode($id_server,$inode);
        else $data = array();

        return $response->getBody()->write(json_encode($data));
    }

}
