<?php

use Illuminate\Database\Migrations\Migration;

class UpdateMisnamedBungee extends Migration
{
    public function up()
    {
        DB::table('service_variables')->select('env_variable')->where('env_variable', 'BUNGE_VERSION')->update([
            'env_variable' => 'BUNGEE_VERSION',
        ]);
    }

    public function down()
    {
    }
}
