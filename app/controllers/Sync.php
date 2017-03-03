<?php namespace Undefined;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

class Sync
{

    public function __construct()
    {
        $this->_items = new \Undefined\Models\Items();
        $this->_accords = new \Undefined\Models\Accords();
    }

    /**
     * Viva la recursion!
     *
     * @param $id_server
     * @param $id_parent
     * @param array $array
     */
    public function viva_la_recursion($id_server, $id_parent, array $array)
    {
        // Remove all files from directory
        $this->_items->deleteInFolder($id_parent);

        $i = 0;
        while ($i < count($array)) {
            //$array[$i]->type

            // Get details about current folder
            $folder = $this->_items->getByInode($id_server, $array[$i]->inode);

            // Data for insertions
            $data = array(
                'id_server' => $id_server,
                'id_parent' => $id_parent,
                'inode' => $array[$i]->inode,
                'name' => $array[$i]->name,
                'time' => date('Y-m-d H:i:s', $array[$i]->time),
                'deleted' => 0
            );

            // If folder is found
            if (empty($folder)) {
                // Make insert and get ID for next step
                $id_folder = $this->_items->insert($data);
                // Insert data into accord_folder table
                $this->_accords->insert(array("id_project" => $this->id_project, "id_folder" => $id_folder));
            } else {
                // Make update
                $this->_items->update($data, array('id' => $folder->id));
                // For nex step
                $id_folder = $folder->id;
            }

            // Run next step of recursion
            $this->viva_la_recursion($id_server, $id_folder, $array[$i]->content);

            $i++;
        }

    }

    public function post(Request $request, Response $response)
    {
        return 'post';
    }

    public function put(Request $request, Response $response)
    {
        return 'put';
    }
}
