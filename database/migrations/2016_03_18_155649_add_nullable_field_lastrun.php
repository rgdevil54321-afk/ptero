<?php

use Illuminate\Database\Migrations\Migration;

class AddNullableFieldLastrun extends Migration
{
    public function up()
    {
        $table = DB::getQueryGrammar()->wrapTable('tasks');
        DB::statement('ALTER TABLE ' . $table . ' CHANGE `last_run` `last_run` TIMESTAMP NULL;');
    }

    public function down()
    {
        $table = DB::getQueryGrammar()->wrapTable('tasks');
        DB::statement('ALTER TABLE ' . $table . ' CHANGE `last_run` `last_run` TIMESTAMP;');
    }
}
