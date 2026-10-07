<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveLegacySFTPInformation extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropUnique(['username']);

            $table->dropColumn('username');
            $table->dropColumn('sftp_password');
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('username')->nullable()->after('image')->unique();
            $table->text('sftp_password')->after('image');
        });
    }
}
