<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class RemoveUserInteraction extends Migration
{
    public function up()
    {
        DB::table('eggs')->update([
            'config_startup' => DB::raw('JSON_REMOVE(config_startup, \'$.userInteraction\')'),
        ]);
    }

    public function down()
    {
        DB::table('eggs')->update([
            'config_startup' => DB::raw('JSON_SET(config_startup, \'$.userInteraction\', JSON_ARRAY())'),
        ]);
    }
}
