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
        //$this->_items->deleteInFolder($id_parent);

        $i = 0;
        while ($i < count($array)) {
            //$array[$i]->type

            // Get details about current folder
            $item = $this->_items->getByInode($this->id_server, $array[$i]->inode);

            // If item is not found
            if (empty($item[0]->id)) {

                $this->_items->id_server = $this->id_server;
                $this->_items->id_parent = $id_parent;
                $this->_items->inode = $array[$i]->inode;
                $this->_items->name = $array[$i]->name;
                $this->_items->time = date('Y-m-d H:i:s', $array[$i]->time);
                $this->_items->deleted = 0;

                // Make insert and get ID for next step
                $id_item = $this->_items->save();

                // Insert data into accord_folder table
                $this->_accords->id_project = $this->id_project;
                $this->_accords->id_item = (string)$id_item;
                $this->_accords->save();

            } else {

                // Insert data into accord_folder table
                $this->_accords->id_project = $this->id_project;
                $this->_accords->id_item = (string)$item[0]->id;
                $this->_accords->save();

                // For nex step
                $id_item = (string)$item[0]->id;
            }

            // Simple array check for the next step
            if (is_array($array[$i]->content) && !empty($array[$i]->content)) {

                error_log("INF: Array not empty");

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

        //error_log($server_ip);

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
            $this->_projects->id_server = $this->id_server;
            $this->_projects->path = $json->name;
            $this->_projects->time_start = date('Y-m-d H:i:s');
            // Make insert and return the ID
            $this->id_project = $this->_projects->save();

        } else {

            error_log('INF: Update project');

            // Set the project ID
            $this->id_project = (string)$project[0]->id;

        }

        // Next we need insert or update folder in database
        $project_folder = $this->_items->getByInode($this->id_server, $json->inode);

        // If project not found in base
        if (empty((string)$project_folder[0]->id)) {

            // The folder name (end slash bug fixed)
            $name = rtrim($json->name, '/');
            $name = explode('/', $name);

            // Insert new item
            $this->_items->id_server = $this->id_server;
            $this->_items->inode = $json->inode;
            $this->_items->name = end($name);
            $this->_items->time = date('Y-m-d H:i:s', $json->time);
            // Make insert and return the ID
            $id_project_folder = $this->_items->save();

            // Save the accords
            $this->_accords->id_project = $this->id_project;
            $this->_accords->id_item = $id_project_folder;
            $this->_accords->save();

            // Set the project directory
            $this->_projects->where(array('id' => $this->id_project));
            $this->_projects->id_folder = $id_project_folder;
            $this->_projects->save();

        } else {

            // Set the work ID
            $id_project_folder = (string)$project_folder[0]->id;

        }

        // Viva la recursion!
        $this->viva_la_recursion($id_project_folder, $json->content);

        // Remove inodes from deleted folders and files
        // TODO: Make deleting from db if inode is empty
        //$this->_items->where(array('inode' => null));
        //$this->_items->deleted = 1;
        //$this->_items->save();
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

            // Replace the event name to ID
            $data[$i]->event = preg_replace($e['desc'], $e['ids'], $json[$i]->event);
            switch ($data[$i]->event) {
                //[0] => IS_EMPTY
                //[1] => INPUT_IS_EMPTY
                //[2] => OUTPUT_IS_EMPTY
                //[3] => IS_CREATED
                case '3':
                    // Get the folder ID
                    $parent = $this->_items->getByInode($this->id_server, $data[$i]->parent);
                    // Data for insertion
                    $new_file = array(
                        'deleted' => 0,
                        'name' => $data[$i]->name,
                        'inode' => $data[$i]->inode,
                        'id_parent' => $parent->id,
                        'time' => date("Y-m-d H:i:s", $data[$i]->time),
                        'id_server' => $this->id_server,
                    );

                    // insert new folder into folders
                    $id_folder = $this->_items->insert($new_file);

                    // Message about new file
                    $desc = "{'parent': '" . $data[$i]->parent . "'}";
                    break;
                //[4] => IS_DELETED
                case '4':
                    // Get the folder ID
                    $folder = $this->_items->getByInode($this->id_server, $data[$i]->inode);
                    // Remove the folder and make inode null
                    $this->_items->update(array('deleted' => 1, 'inode' => null), array('id' => $folder->id));

                    // Message about new file
                    $desc = "{'time': '" . $data[$i]->time . "'}";
                    break;
                //[5] => NEW_NAME
                case '5':
                    // Action details
                    $new_name = array('name' => $data[$i]->name);

                    // Get the folder ID
                    $folder = $this->_items->getByInode($this->id_server, $data[$i]->inode);
                    // Remove the folder and make inode null
                    $this->_items->update($new_name, array('id' => $folder->id));

                    // Description
                    $desc = "{'old': '" . $folder->name . "', 'new': '" . $data[$i]->name . "'}";
                    break;
                //[6] => NEW_TIME
                case '6':
                    // Action details
                    $new_time = array('time' => $data[$i]->time);

                    // Get the folder ID
                    $folder = $this->_items->getByInode($this->id_server, $data[$i]->inode);
                    // Remove the folder and make inode null
                    $this->_items->update($new_time, array('id' => $folder->id));

                    // Description
                    $desc = "{'time': '" . $data[$i]->name . "'}";
                    break;
                //[7] => NEW_HASH
                case '7':
                    // Action details
                    $new_hash = array('time' => $data[$i]->crc);

                    // Get the folder ID
                    $folder = $this->_items->getByInode($this->id_server, $data[$i]->inode);
                    // Remove the folder and make inode null
                    $this->_items->update($new_hash, array('id' => $folder->id));

                    // Description
                    $desc = "{'hash': '" . $data[$i]->crc . "'}";
                    break;
            }

            // Small check if files ids is not empty
            if (!empty($id_file) || !empty($id_folder)) {

                // Array for insertion
                $insert = array(
                    'id_event' => $data[$i]->event,
                    'time' => date('Y-m-d H:i:s', $data[$i]->time),
                    'description' => $desc
                );

                // Yet another file or folder selector
                switch ($data[$i]->type) {
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
