<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignSubusers extends Migration
{
    public function up()
    {
        Schema::table('subusers', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('server_id')->references('id')->on('servers');
        });
    }

    public function down()
    {
        Schema::table('subusers', function (Blueprint $table) {
            $table->dropForeign('subusers_user_id_foreign');
            $table->dropForeign('subusers_server_id_foreign');

            $table->dropIndex('subusers_user_id_foreign');
            $table->dropIndex('subusers_server_id_foreign');
        });
    }
}
