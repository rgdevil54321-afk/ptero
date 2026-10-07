<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForeignKeysToMountServerTable extends Migration
{
    public function up()
    {
        Schema::table('mount_server', function (Blueprint $table) {
            $table->unsignedInteger('server_id')->change();
            $table->unsignedInteger('mount_id')->change();
        });

        $servers = DB::table('servers')->select('id')->pluck('id')->toArray();
        $mounts = DB::table('mounts')->select('id')->pluck('id')->toArray();

        DB::table('mount_server')
            ->select('server_id', 'mount_id')
            ->whereNotIn('server_id', $servers)
            ->orWhereNotIn('mount_id', $mounts)
            ->delete();

        Schema::table('mount_server', function (Blueprint $table) {
            $table->foreign('server_id')
                ->references('id')
                ->on('servers')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
            $table->foreign('mount_id')->references('id')
                ->on('mounts')
                ->cascadeOnDelete()
                ->cascadeOnUpdate();
        });
    }

    public function down()
    {
        Schema::table('mount_server', function (Blueprint $table) {
            $table->dropForeign(['server_id']);
            $table->dropForeign(['mount_id']);
        });
    }
}
