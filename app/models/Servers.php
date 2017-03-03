<?php namespace Undefined\Models;

use \Illuminate\Database\Eloquent\Model;

class Servers extends Model
{
    protected $table = 'servers';
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
    public function getByIP($ip)
    {
        return $this
            ->where('ip', $ip)
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
