<?php namespace Undefined\Models;

use \Illuminate\Database\Eloquent\Model;

class Accords extends Model
{
    protected $table = 'accords';
    protected $primaryKey = null;
    public $timestamps = false;
    public $incrementing = false;

    /**
     * Select all rows by project ID
     *
     * @param int $id_project
     * @param int|null $id_type
     * @return mixed
     */
    public function getItemsByProject($id_project, $id_type = null)
    {
        $query = $this->where('id_project', $id_project);

        if (empty($id_type)) $return = $query;
        else $return = $query->where('id_type', $id_type);

        return $return->get();
    }

}
