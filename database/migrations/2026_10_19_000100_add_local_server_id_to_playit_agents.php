<?php

/*
 * Turtle Game Panel - let one playit agent key serve either a Pterodactyl
 * server (server_uuid) or a native "local node" server (local_server_id).
 *
 * A key assigned to a local server keeps server_uuid NULL, which is why the
 * pool queries in PlayitService also filter on local_server_id being NULL.
 *
 * Additive + idempotent.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('playit_agents') || Schema::hasColumn('playit_agents', 'local_server_id')) {
            return;
        }

        Schema::table('playit_agents', function (Blueprint $table) {
            $table->unsignedInteger('local_server_id')->nullable()->after('server_id');
            $table->index('local_server_id');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('playit_agents') || !Schema::hasColumn('playit_agents', 'local_server_id')) {
            return;
        }

        Schema::table('playit_agents', function (Blueprint $table) {
            $table->dropIndex(['local_server_id']);
            $table->dropColumn('local_server_id');
        });
    }
};
