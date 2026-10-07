<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

class DropGoogleAnalytics extends Migration
{
    public function up()
    {
        DB::table('settings')->where('key', 'settings::app:analytics')->delete();
    }

    public function down()
    {
        DB::table('settings')->insert(
            [
            'key' => 'settings::app:analytics',
            ]
        );
    }
}
