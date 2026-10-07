<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSupportForLockingABackup extends Migration
{
    public function up()
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->unsignedTinyInteger('is_locked')->after('is_successful')->default(0);
        });
    }

    public function down()
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropColumn('is_locked');
        });
    }
}
