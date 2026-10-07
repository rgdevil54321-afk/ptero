<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('agent_server_transfers', function (Blueprint $table) {
            $table->json('backup_uuids')->nullable()->after('include_native_backups');
        });
    }

    public function down(): void
    {
        Schema::table('agent_server_transfers', function (Blueprint $table) {
            $table->dropColumn('backup_uuids');
        });
    }
};
