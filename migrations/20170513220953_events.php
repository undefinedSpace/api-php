<?php

use \Undefined\Migrations\Migration;

class Events extends Migration
{

    public function up()
    {
        $this->schema->create('events', function(Illuminate\Database\Schema\Blueprint $table){
            $table->increments('id');
            $table->text('description');
        });
    }

    public function down()
    {
        $this->schema->drop('events');
    }

}
