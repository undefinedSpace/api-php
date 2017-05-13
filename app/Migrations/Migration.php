<?php namespace Undefined\Migrations;

use \Illuminate\Database\Capsule\Manager as Capsule;
use \Phinx\Migration\AbstractMigration;

class Migration extends AbstractMigration {
    /** @var \Illuminate\Database\Capsule\Manager $capsule */
    public $capsule;
    /** @var \Illuminate\Database\Schema\Builder $capsule */
    public $schema;

    public function init()
    {
        // Database information
        $settings = \DrMVC\Core\Config::load('database');

        $this->capsule = new Capsule;
        $this->capsule->addConnection($settings);
        $this->capsule->bootEloquent();
        $this->capsule->setAsGlobal();
        $this->schema = $this->capsule->schema();
    }
}
