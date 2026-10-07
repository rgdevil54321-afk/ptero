<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ModifyIpStorageMethod extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->mediumInteger('allocation')->unsigned()->after('oom_disabled');
        });

        $servers = DB::select('SELECT id, ip, port, node FROM servers');
        foreach ($servers as $server) {
            $allocation = DB::select(
                'SELECT id FROM allocations WHERE ip = :ip AND port = :port AND node = :node',
                [
                    'ip' => $server->ip,
                    'port' => $server->port,
                    'node' => $server->node,
                ]
            );

            if (isset($allocation[0])) {
                DB::update(
                    'UPDATE servers SET allocation = :alocid WHERE id = :id',
                    [
                        'alocid' => $allocation[0]->id,
                        'id' => $server->id,
                    ]
                );
            }
        }

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('ip');
            $table->dropColumn('port');
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->text('ip')->after('allocation');
            $table->integer('port')->unsigned()->after('ip');
        });

        $servers = DB::select('SELECT id, allocation FROM servers');
        foreach ($servers as $server) {
            $allocation = DB::select('SELECT * FROM allocations WHERE id = :alocid', ['alocid' => $server->allocation]);

            if (isset($allocation[0])) {
                DB::update(
                    'UPDATE servers SET ip = :ip, port = :port WHERE id = :id',
                    [
                        'ip' => $allocation[0]->ip,
                        'port' => $allocation[0]->port,
                        'id' => $server->id,
                    ]
                );
            }
        }

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('allocation');
        });
    }
}
