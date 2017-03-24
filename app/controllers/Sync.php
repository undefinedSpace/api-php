<?php namespace Undefined;

use \Psr\Http\Message\ServerRequestInterface as Request;
use \Psr\Http\Message\ResponseInterface as Response;

use \Undefined\Models\Changes;
use \Undefined\Models\Events;
use \Undefined\Models\Items;
use \Undefined\Models\Accords;
use \Undefined\Models\Projects;
use \Undefined\Models\Servers;

class Sync
{
    public $id_project;
    public $id_server;

    public function __construct()
    {
        $this->_items = new Items();
        $this->_accords = new Accords();
        $this->_events = new Events();
        $this->_changes = new Changes();
        $this->_projects = new Projects();
        $this->_servers = new Servers();
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
        Items::where('id_parent', $id_parent)
            ->update([
                'deleted' => 1
            ]);

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
                $items = new Items();
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
                $items = new Accords();
                $items->id_item = $id_item;
                $items->id_project = $this->id_project;
                $items->save();

            } else {
                //error_log("INF: id_item is " . $id_item);

                // Update items details by id
                Items::where('id', $id_item)
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

            error_log('INF: New project');

            // Create new project
            $projects = new Projects();
            $projects->id_server = $this->id_server;
            $projects->path = $json->name;
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
            $items = new Items();
            $items->id_server = $this->id_server;
            $items->id_type = 0;
            $items->hash = null;
            $items->inode = $json->inode;
            $items->name = end($name);
            $items->time = date('Y-m-d H:i:s', $json->time);
            $items->deleted = 0;
            $items->save();

            // Get the project folder
            $id_project_folder = (string)$items->id;

            // Insert data into accords table
            $accords = new Accords();
            $accords->id_project = $this->id_project;
            $accords->id_item = $id_project_folder;
            $accords->save();

            // Set the project directory
            Projects::where('id', $this->id_project)
                ->update([
                    'id_item' => $id_project_folder
                ]);

        }

        // Viva la recursion!
        $this->viva_la_recursion($id_project_folder, $json->content);

        // Remove inodes from deleted folders and files
        Items::where('deleted', 1)->update(['inode' => null]);
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

            error_log(print_r($json, true));

            switch ($json[$i]->event) {
                //[0] => IS_EMPTY
                //[1] => INPUT_IS_EMPTY
                //[2] => OUTPUT_IS_EMPTY
                //[3] => IS_CREATED
                case '3':

                    $id_parent = $this->_items
                        ->where('inode', $json[$i]->parent)
                        ->where('id_type', 0)
                        ->get();

                    // Insert new item
                    $items = new Items();
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

                    // Message about new file
                    $desc = "{'inode': '" . $json[$i]->parent . "', 'name': '" . $json[$i]->name . "', 'hash': '" . $json[$i]->crc . "', 'time': '" . $json[$i]->time . "'}";
                    break;
                //[4] => IS_DELETED
                case '4':

//                    $id_parent = $this->_items
//                        ->where('inode', $json[$i]->parent)
//                        ->where('id_type', 0)
//                        ->get();

                    // Set the project directory
                    Items::where('inode', $json[$i]->inode)
                        //->where('id_parent', (string)$id_parent[0]->id)
                        ->update(['deleted' => 1, 'inode' => null]);

                    // Message about new file
                    $desc = "{'time': '" . $json[$i]->time . "'}";
                    break;
                //[5] => NEW_NAME
                case '5':
                    // Description
                    $desc = "{'name_old': '" . $folder->name . "', 'name_new': '" . $json[$i]->name . "'}";
                    break;
                //[6] => NEW_TIME
                case '6':
                    // Description
                    $desc = "{'time': '" . $json[$i]->name . "'}";
                    break;
                //[7] => NEW_HASH
                case '7':
                    // Description
                    $desc = "{'hash': '" . $json[$i]->crc . "'}";
                    break;
            }

            // Small check if files ids is not empty
            if (!empty($id_item)) {

                // Array for insertion
                $insert = [
                    'id_event' => $json[$i]->event,
                    'time' => date('Y-m-d H:i:s', $json[$i]->time),
                    'description' => $desc
                ];

                // Yet another file or folder selector
                switch ($json[$i]->type) {
                    case 'folder':
                        $insert['id_type'] = '0';
                        break;
                    case 'file':
                        $insert['id_type'] = '1';
                        break;
                }

                // Insert new event about file
                $this->_changes->insert($insert);
            }

            $i++;
        }
    }
}
