<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddBackupLimitToServers extends Migration
{
    public function up()
    {
        $db = config('database.default');
        $results = DB::select('SELECT * FROM information_schema.COLUMNS WHERE TABLE_SCHEMA = ? AND TABLE_NAME = \'servers\' AND COLUMN_NAME = \'backup_limit\'', [
            config("database.connections.{$db}.database"),
        ]);

        if (count($results) === 1) {
            Schema::table('servers', function (Blueprint $table) {
                $table->unsignedInteger('backup_limit')->default(0)->change();
            });
        } else {
            Schema::table('servers', function (Blueprint $table) {
                $table->unsignedInteger('backup_limit')->default(0)->after('database_limit');
            });
        }
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('backup_limit');
        });
    }
}
