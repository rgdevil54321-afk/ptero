<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RenamePermissionsColumn extends Migration
{
    public function up()
    {
        Schema::table('permissions', function (Blueprint $table) {
            $table->renameColumn('permissions', 'permission');
        });
    }

    public function down()
    {
        Schema::table('permissions', function (Blueprint $table) {
        });
    }
}
