<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignPermissions extends Migration
{
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('server_id')->references('id')->on('servers');
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->dropForeign('permissions_user_id_foreign');
            $table->dropForeign('permissions_server_id_foreign');

            $table->dropIndex('permissions_user_id_foreign');
            $table->dropIndex('permissions_server_id_foreign');
        });
    }
}
