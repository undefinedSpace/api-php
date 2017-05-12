<?php namespace Undefined\Models;

use \Illuminate\Database\Eloquent\Model;

class Items extends Model
{
    protected $table = 'items';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * Get file by ID
     *
     * @param $id
     * @return mixed
     */
    public function getByID($id)
    {
        return $this
            ->where('id', $id)
            ->get();
    }

    /**
     * Get file by server id and inode
     *
     * @param $id_server
     * @param $inode
     * @return mixed
     */
    public function getByInode($id_server, $inode)
    {
        return $this
            ->where('id_server', $id_server)
            ->where('inode', $inode)
            ->get();
    }

    /**
     * Return all files
     *
     * @return mixed
     */
    public function getAll()
    {
        return $this->get();
    }

    /**
     * Delete all files from some folder
     *
     * @param $id_parent
     * @return mixed
     */
    public function deleteInFolder($id_parent) {
        return $this
            ->where('id_parent', $id_parent)
            ->delete();
    }
}
