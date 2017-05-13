<?php namespace Undefined\Controllers;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Undefined\Models\Changes as Model_Changes;
use \Undefined\Models\Events as Model_Events;
use \Undefined\Models\Items as Model_Items;
use \Undefined\Models\Accords as Model_Accords;
use \Undefined\Models\Projects as Model_Projects;
use \Undefined\Models\Servers as Model_Servers;

class Sync
{
    public $id_project;
    public $id_server;

    public function __construct()
    {
        $this->_items = new Model_Items();
        $this->_accords = new Model_Accords();
        $this->_events = new Model_Events();
        $this->_changes = new Model_Changes();
        $this->_projects = new Model_Projects();
        $this->_servers = new Model_Servers();
    }

    /**
     * Viva la recursion!
     *
     * @param $id_parent
     * @param $array
     */
    public function viva_la_recursion($id_parent, $array)
    {
        // Remove all files from directory
        Model_Items::where('id_parent', $id_parent)
            ->update(['deleted' => 1]);

        $i = 0;
        while ($i < count($array)) {
            // Get details about current folder
            $item = $this->_items->getByInode($this->id_server, $array[$i]->inode);
            $id_item = (string)$item[0]->id;

            // Get id of type
            $id_type = preg_replace(['/file/iu', '/folder/iu'], ['1', '0'], $array[$i]->type);

            // If is folder then hash is null
            if ($id_type == 0) $hash = null; else $hash = $array[$i]->crc;

            if (empty($id_item)) {
                //error_log("INF: id_item is empty");

                // Insert new item
                $items = new Model_Items();
                $items->id_parent = $id_parent;
                $items->id_server = $this->id_server;
                $items->id_type = $id_type;
                $items->name = $array[$i]->name;
                $items->inode = $array[$i]->inode;
                $items->time = date("Y-m-d H:i:s", strtotime($array[$i]->time));
                $items->hash = $hash;
                $items->deleted = 0;
                $items->save();

                // Last insert id
                $id_item = (string)$items->id;

                // Set the accord project
                $items = new Model_Accords();
                $items->id_item = $id_item;
                $items->id_project = $this->id_project;
                $items->save();

            } else {
                //error_log("INF: id_item is " . $id_item);

                // Update items details by id
                Model_Items::where('id', $id_item)
                    ->update([
                        'id_parent' => $id_parent,
                        'id_server' => $this->id_server,
                        'id_type' => $id_type,
                        'name' => $array[$i]->name,
                        'inode' => $array[$i]->inode,
                        'time' => date("Y-m-d H:i:s", strtotime($array[$i]->time)),
                        'hash' => $hash,
                        'deleted' => 0,
                    ]);
            }

            // If id_type is folder
            if ($id_type == '0' && !empty($id_item)) {

                error_log("INF: It's a folder");
                error_log("INF: id_parent is " . $id_item);

                // Run next step of recursion
                $this->viva_la_recursion($id_item, $array[$i]->content);
            }

            $i++;
        }

    }

    public function post(Request $request, Response $response)
    {
        // Server IP from env
        $server_ip = $_SERVER['REMOTE_ADDR'];

        // Request to server
        $request = file_get_contents('php://input');

        // Get server object
        $server = $this->_servers->getByIP($server_ip);
        if (empty($server)) die(json_encode(['status' => 'error']));
        $this->id_server = (string)$server[0]->id;

        // Translate json to object of array
        $json = json_decode($request);

        // Get project from base as object
        $project = $this->_projects->getByPath($json->name);

        // If project not found in base
        if (empty((string)$project[0]->id)) {

            // Create new project
            $projects = new Model_Projects();
            $projects->id_server = $this->id_server;
            $projects->path = $string = rtrim($json->name, '/');
            $projects->time_start = date('Y-m-d H:i:s');
            $projects->save();

            // Get the project id
            $this->id_project = (string)$projects->id;

            error_log('INF: New project ID ' . $this->id_project);

        } else {

            error_log('INF: Update project ' . (string)$project[0]->id);

            // Set the project ID
            $this->id_project = (string)$project[0]->id;

        }

        // Next we need insert or update folder in database
        $project_folder = $this->_items->getByInode($this->id_server, $json->inode);
        $id_project_folder = (string)$project_folder[0]->id;

        // If project not found in base
        if (empty($id_project_folder)) {

            // The folder name (end slash bug fixed)
            $name = rtrim($json->name, '/');
            $name = explode('/', $name);

            // Insert new item
            $items = new Model_Items();
            $items->id_server = $this->id_server;
            $items->id_parent = null;
            $items->id_type = 0;
            $items->hash = null;
            $items->inode = $json->inode;
            $items->name = end($name);
            $items->time = date('Y-m-d H:i:s', $json->time);
            $items->deleted = 0;
            $items->save();

            // Get the project folder
            $id_project_folder = (string)$items->id;

            // Set the project directory
            Model_Projects::where('id', $this->id_project)->update(['id_item' => $id_project_folder]);
        }

        // Search project folder in accords
        $project_accords = $this->_accords->getByItemProject($id_project_folder, $this->id_project);

        // If line is not found then make insert
        if (empty((string)$project_accords[0]->id_item)) {
            // Insert data into accords table
            $accords = new Model_Accords();
            $accords->id_item = $id_project_folder;
            $accords->id_project = $this->id_project;
            $accords->save();
        }

        // Viva la recursion!
        $this->viva_la_recursion($id_project_folder, $json->content);

        // Remove inodes from deleted folders and files
        Model_Items::where('deleted', 1)->update(['inode' => null]);
    }

    public function put(Request $request, Response $response)
    {
        // Server IP from env
        $server_ip = $_SERVER['REMOTE_ADDR'];

        // Request to server
        $request = file_get_contents('php://input');

        // Get server object
        $server = $this->_servers->getByIP($server_ip);
        if (empty($server)) die(json_encode(['status' => 'error']));
        $this->id_server = $server[0]->id;

        // Translate json to object of array
        $json = json_decode($request);

        // Get all events from database
        $events = $this->_events->getAll();

        // Now we need two array for preg_replace
        $e = array();
        foreach ($events as $event) {
            $e['desc'][] = '/' . $event->description . '/';
            $e['ids'][] = $event->id;
        }

        // If we get something
        if (!empty($json)) {
            // Update time of project
            $this->_projects
                ->where(array('id' => $this->id_project))
                ->update(array('time_start' => date('Y-m-d H:i:s', $json[0]->time)));
        }

        $i = 0;
        while ($i < count($json)) {
            // Clean the description
            $desc = null;

            // Get id of type
            $json[$i]->type = preg_replace(['/file/iu', '/folder/iu'], ['1', '0'], $json[$i]->type);

            // Replace the event name to ID
            $json[$i]->event = preg_replace($e['desc'], $e['ids'], $json[$i]->event);

            // Choose the event by ID
            switch ($json[$i]->event) {
                //[0] => IS_EMPTY
                //[1] => INPUT_IS_EMPTY
                //[2] => OUTPUT_IS_EMPTY
                //[3] => IS_CREATED
                case 3:

                    $id_parent = $this->_items
                        ->where('inode', $json[$i]->parent)
                        ->where('id_type', 0)
                        ->where('id_server', $this->id_server)
                        ->get();

                    // Insert new item
                    $items = new Model_Items();
                    $items->id_server = $this->id_server;
                    $items->id_parent = (string)$id_parent[0]->id;
                    $items->id_type = $json[$i]->type;
                    $items->hash = $json[$i]->crc;
                    $items->inode = $json[$i]->inode;
                    $items->name = $json[$i]->name;
                    $items->time = date('Y-m-d H:i:s', $json[$i]->time);
                    $items->deleted = 0;
                    $items->save();

                    $id_item = (string)$items->id;

                    // Set the accord project
                    $accords = new Model_Accords();
                    $accords->id_item = $id_item;
                    $accords->id_project = $this->id_project;
                    $accords->save();

                    // Message about new file
                    $desc = "{'inode': '" . $json[$i]->parent . "', 'name': '" . $json[$i]->name . "', 'hash': '" . $json[$i]->crc . "', 'time': '" . $json[$i]->time . "'}";
                    break;
                //[4] => IS_DELETED
                case 4:

                    // Get details about current item
                    $item = $this->_items
                        ->where('inode', $json[$i]->inode)
                        ->where('id_server', $this->id_server)
                        ->get();

                    // Set the item
                    $id_item = (string)$item[0]->id;

                    // Parent ID
                    $id_parent = $this->_items
                        ->where('inode', $json[$i]->parent)
                        ->where('id_type', 0)
                        ->get();

                    // Set the project directory
                    Model_Items::where('inode', $json[$i]->inode)
                        ->where('id_parent', (string)$id_parent[0]->id)
                        ->where('id_server', $this->id_server)
                        ->update(['deleted' => 1, 'inode' => null]);

                    // Message about new file
                    $desc = "{'time': '" . $json[$i]->time . "'}";
                    break;
                //[5] => NEW_NAME
                case 5:

                    // Get details about current item
                    $item = $this->_items
                        ->where('inode', $json[$i]->inode)
                        ->where('id_server', $this->id_server)
                        ->get();

                    // Set the item
                    $id_item = (string)$item[0]->id;

                    // Set the project directory
                    Model_Items::where('id', $id_item)
                        ->where('id_server', $this->id_server)
                        ->update(['name' =>  $json[$i]->name]);

                    // Description
                    $desc = "{'name_old': '" . $item[0]->name . "', 'name_new': '" . $json[$i]->name . "'}";
                    break;
                //[6] => NEW_TIME
                case 6:

                    // Get details about current item
                    $item = $this->_items
                        ->where('inode', $json[$i]->inode)
                        ->where('id_server', $this->id_server)
                        ->get();

                    // Set the item
                    $id_item = (string)$item[0]->id;

                    // Set the project directory
                    Model_Items::where('id', $id_item)
                        ->where('id_server', $this->id_server)
                        ->update(['time' =>  $json[$i]->time]);

                    // Description
                    $desc = "{'time': '" . $json[$i]->name . "'}";
                    break;
                //[7] => NEW_HASH
                case 7:

                    // Get details about current item
                    $item = $this->_items
                        ->where('inode', $json[$i]->inode)
                        ->where('id_server', $this->id_server)
                        ->get();

                    // Set the item
                    $id_item = (string)$item[0]->id;

                    // Set the project directory
                    Model_Items::where('id', $id_item)
                        ->where('id_server', $this->id_server)
                        ->update(['hash' =>  $json[$i]->crc]);

                    // Description
                    $desc = "{'hash': '" . $json[$i]->crc . "'}";
                    break;
            }

            // Small check if files ids is not empty
            if (!empty($id_item)) {
                // Insert new item
                $changes = new Model_Changes();
                $changes->id_item = $id_item;
                $changes->id_event = $json[$i]->event;
                $changes->description = $desc;
                $changes->time = date('Y-m-d H:i:s', $json[$i]->time);
                $changes->save();
            }

            $i++;
        }
    }
}
