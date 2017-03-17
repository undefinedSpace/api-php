<?php namespace Undefined\Models;

use \Illuminate\Database\Eloquent\Model;

class Projects extends Model
{
    protected $table = 'projects';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * Get server by ID
     *
     * @param int $id
     * @return mixed
     */
    public function getByID($id)
    {
        return $this
            ->where('id', $id)
            ->get();
    }

    /**
     * Get server by path
     *
     * @param string $path
     * @return mixed
     */
    public function getByPath($path)
    {
        return $this
            ->where('path', $path)
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

}
