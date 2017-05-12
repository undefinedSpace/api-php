<?php namespace Undefined\Models;

use \Illuminate\Database\Eloquent\Model;

class Changes extends Model
{
    protected $table = 'changes';
    protected $primaryKey = 'id';
    public $timestamps = false;

    /**
     * Get changes by file ID
     *
     * @param int $id
     * @return mixed
     */
    public function getChangesByID($id)
    {
        return $this
            ->where('id_item', $id)
            ->get();
    }
}
