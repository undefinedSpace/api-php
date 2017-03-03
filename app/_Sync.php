<?php namespace Application\Controllers;

/**
 * Class Sync
 * @package Application\Controllers
 */
class Sync extends External
{
    /**
     * @var
     */
    public $id_project;

    /**
     * Sync constructor
     */
    public function __construct()
    {
        parent::__construct();
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
        $this->_files->deleteFilesInFolder($id_parent);
        // Remove all folders from directory
        $this->_folders->deleteFoldersInFolder($id_parent);

        $i = 0;
        while ($i < count($array)) {
            switch ($array[$i]->type) {
                case 'folder':
                    // Get details about current folder
                    $folder = $this->_folders->getByInode($id_server, $array[$i]->inode);

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
                        $id_folder = $this->_folders->insert($data);
                        // Insert data into accord_folder table
                        $this->_accord_folders->insert(array("id_project" => $this->id_project, "id_folder" => $id_folder));
                    } else {
                        // Make update
                        $this->_folders->update($data, array('id' => $folder->id));
                        // For nex step
                        $id_folder = $folder->id;
                    }

                    // Run next step of recursion
                    $this->viva_la_recursion($id_server, $id_folder, $array[$i]->content);

                    break;
                case 'file':
                    $file = $this->_files->getByInode($id_server, $array[$i]->inode);

                    // Data for update or insert
                    $data = array(
                        'id_server' => $id_server,
                        'id_parent' => $id_parent,
                        'inode' => $array[$i]->inode,
                        'name' => $array[$i]->name,
                        // Hashsum, should be based on CRC16
                        'hash' => $array[$i]->crc,
                        'time' => date('Y-m-d H:i:s', $array[$i]->time),
                        'deleted' => 0
                    );

                    // If file is found
                    if (empty($file)) {
                        // Make insertion into database
                        $id_file = $this->_files->insert($data);
                        // Insert data into accord_folder table
                        $this->_accord_files->insert(array("id_project" => $this->id_project, "id_file" => $id_file));
                    } else $this->_files->update($data, array('id' => $file->id));

                    break;
            }
            $i++;
        }

    }

    /**
     * Init new sync
     */
    public function action_index()
    {
        // Server IP from env
        $server_ip = $_SERVER['REMOTE_ADDR'];

        // Request to server
        $request = file_get_contents('php://input');

        // If server ip is empty
        if (empty($server_ip)) $this->response('101');
        // Get server object
        $server = $this->_servers->getByIP($server_ip);
        // If server object is empty show error
        if (empty($server)) $this->response('104');
        // Set the server ID
        $id_server = $server->id;

        // If post is empty
        if (!empty($input)) {
            header('Content-type: application/json');
            $this->response('200');
        } else {
            // If request is json
            if ($this->isJson($request)) {
                // Translate json to object of array
                $data = json_decode($request);

                // Select the work mode
                switch (true) {
                    // This mean we on inital stage
                    case (is_object($data)):
                        // Get project from base as object
                        $project = $this->_projects->getByPath($data->name);

                        // If project not found in base
                        if (empty($project)) {
                            // Make insert and return the ID
                            $this->id_project = $this->_projects->insert(array("id_server" => $id_server, "path" => $data->name, "time_start" => date('Y-m-d H:i:s')));
                        } else {
                            // Set the project ID
                            $this->id_project = $project->id;
                        }

                        // Next we need insert or update folder in database
                        $project_folder = $this->_folders->getByInode($server->id, $data->inode);

                        // If project not found in base
                        if (empty($project_folder)) {
                            // The folder name (end slash bug fixed)
                            $name = rtrim($data->name, '/');
                            $name = explode('/', $name);
                            // Make insert and return the ID
                            $id_project_folder = $this->_folders->insert(array("id_server" => $server->id, "inode" => $data->inode, "name" => end($name), "time" => date('Y-m-d H:i:s', $data->time)));
                            // Insert data into accord_folder table
                            $this->_accord_folders->insert(array("id_project" => $this->id_project, "id_folder" => $id_project_folder));
                        } else {
                            // Set the work ID
                            $id_project_folder = $project_folder->id;
                        }

                        // Update project folder
                        $this->_projects->update(array('id_folder' => $id_project_folder), array('id' => $this->id_project));

                        // Viva la recursion!
                        $this->viva_la_recursion($id_server, $id_project_folder, $data->content);

                        // Remove inodes from deleted folders and files
                        $this->_folders->update(array('inode' => null), array('deleted' => 1));
                        $this->_files->update(array('inode' => null), array('deleted' => 1));
                        break;

                    // This mean we on update stage
                    case (is_array($data)):

                        // Get all events from database
                        $events = $this->_events->getAll();
                        // Now we need two array for preg_replace
                        $e = array();
                        foreach ($events as $event) {
                            $e['desc'][] = '/' . $event->description . '/';
                            $e['ids'][] = $event->id;
                        }

                        // If we get something
                        if (!empty($data)) {
                            // Update time of project
                            $this->_projects->update(
                                array('time_start' => date('Y-m-d H:i:s', $data[0]->time)),
                                array('id' => $this->id_project)
                            );
                        }

                        $i = 0;
                        while ($i < count($data)) {
                            // Clean the description
                            $desc = null;

                            // Replace the event name to ID
                            $data[$i]->event = preg_replace($e['desc'], $e['ids'], $data[$i]->event);
                            switch ($data[$i]->event) {
                                //[0] => IS_EMPTY
                                //[1] => INPUT_IS_EMPTY
                                //[2] => OUTPUT_IS_EMPTY
                                //[3] => IS_CREATED
                                case '3';
                                    // Get the folder ID
                                    $parent = $this->_folders->getByInode($id_server, $data[$i]->parent);
                                    // Data for insertion
                                    $new_file = array(
                                        'deleted' => 0,
                                        'name' => $data[$i]->name,
                                        'inode' => $data[$i]->inode,
                                        'id_parent' => $parent->id,
                                        'time' => date("Y-m-d H:i:s", $data[$i]->time),
                                        'id_server' => $id_server,
                                    );

                                    switch ($data[$i]->type) {
                                        case 'folder':
                                            // insert new folder into folders
                                            $id_folder = $this->_folders->insert($new_file);
                                            break;
                                        case 'file':
                                            // File of hash
                                            $new_file['hash'] = $data[$i]->crc;
                                            // Insert new file into files
                                            $id_file = $this->_files->insert($new_file);
                                            break;
                                    }
                                    // Message about new file
                                    $desc = "{'parent': '" . $data[$i]->parent . "'}";
                                    break;
                                //[4] => IS_DELETED
                                case '4';
                                    switch ($data[$i]->type) {
                                        case 'folder':
                                            // Get the folder ID
                                            $folder = $this->_folders->getByInode($id_server, $data[$i]->inode);
                                            // Remove the folder and make inode null
                                            $this->_folders->update(array('deleted' => 1, 'inode' => null), array('id' => $folder->id));
                                            break;
                                        case 'file':
                                            // Get the file ID
                                            // TODO: Get file by parent inode and file_inode, because in system can be hardlinks
                                            // TODO: hardlink detector
                                            $file = $this->_files->getByInode($id_server, $data[$i]->inode);
                                            // Remove the file and make inode null
                                            $this->_files->update(array('deleted' => 1, 'inode' => null), array('id' => $file->id));
                                            break;
                                    }
                                    // Message about new file
                                    $desc = "{'time': '" . $data[$i]->time . "'}";
                                    break;
                                //[5] => NEW_NAME
                                case '5';
                                    // Action details
                                    $new_name = array('name' => $data[$i]->name);

                                    switch ($data[$i]->type) {
                                        case 'folder':
                                            // Get the folder ID
                                            $folder = $this->_folders->getByInode($id_server, $data[$i]->inode);
                                            // Remove the folder and make inode null
                                            $this->_folders->update($new_name, array('id' => $folder->id));
                                            // Description
                                            $desc = "{'old': '" . $folder->name . "', 'new': '" . $data[$i]->name . "'}";
                                            break;
                                        case 'file':
                                            // Get the file ID
                                            $file = $this->_files->getByInode($id_server, $data[$i]->inode);
                                            // Remove the file and make inode null
                                            $this->_files->update($new_name, array('id' => $file->id));
                                            // Description
                                            $desc = "{'old': '" . $file->name . "', 'new': '" . $data[$i]->name . "'}";
                                            break;
                                    }
                                    break;
                                //[6] => NEW_TIME
                                case '6';
                                    // Action details
                                    $new_time = array('time' => $data[$i]->time);

                                    switch ($data[$i]->type) {
                                        case 'folder':
                                            // Get the folder ID
                                            $folder = $this->_folders->getByInode($id_server, $data[$i]->inode);
                                            // Remove the folder and make inode null
                                            $this->_folders->update($new_time, array('id' => $folder->id));
                                            break;
                                        case 'file':
                                            // Get the file ID
                                            $file = $this->_files->getByInode($id_server, $data[$i]->inode);
                                            // Remove the file and make inode null
                                            $this->_files->update($new_time, array('id' => $file->id));
                                            break;
                                    }
                                    // Description
                                    $desc = "{'time': '" . $data[$i]->name . "'}";
                                    break;

                                //[7] => NEW_HASH
                                case '7';
                                    // Action details
                                    $new_hash = array('time' => $data[$i]->crc);

                                    switch ($data[$i]->type) {
                                        case 'folder':
                                            // Get the folder ID
                                            $folder = $this->_folders->getByInode($id_server, $data[$i]->inode);
                                            // Remove the folder and make inode null
                                            $this->_folders->update($new_hash, array('id' => $folder->id));
                                            break;
                                        case 'file':
                                            // Get the file ID
                                            $file = $this->_files->getByInode($id_server, $data[$i]->inode);
                                            // Remove the file and make inode null
                                            $this->_files->update($new_hash, array('id' => $file->id));
                                            break;
                                    }
                                    // Description
                                    $desc = "{'hash': '" . $data[$i]->crc . "'}";
                                    break;
                            }

                            // Small check if files ids is not empty
                            if (!empty($id_file) || !empty($id_folder)) {

                                // Array for insertion
                                $insert = array('id_event' => $data[$i]->event, 'time' => date('Y-m-d H:i:s', $data[$i]->time), 'description' => $desc);

                                // Yet another file or folder selector
                                switch ($data[$i]->type) {
                                    case 'folder':
                                        $insert['id_folder'] = $id_folder;
                                        // Insert new event about folder
                                        $this->_folder_changes->insert($insert);
                                        break;
                                    case 'file':
                                        $insert['id_file'] = $id_file;
                                        // Insert new event about file
                                        $this->_file_changes->insert($insert);
                                        break;
                                }
                            }

                            $i++;
                        }

                        break;
                }
            }
        }

    }

}
