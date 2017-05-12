<?php namespace Undefined\Models;

use \Illuminate\Database\Eloquent\Model;

class Events extends Model
{
    protected $table = 'events';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * Get event by ID
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
     * Get all events
     *
     * @return mixed
     */
    public function getAll()
    {
        return $this->get();
    }
}
