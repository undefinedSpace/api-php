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
     * Get server by IP
     *
     * @param string $ip
     * @return mixed
     */
    public function getByPath($ip)
    {
        return $this
            ->where('path', $ip)
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
