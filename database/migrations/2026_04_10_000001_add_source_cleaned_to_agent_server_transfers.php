<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('agent_server_transfers', function (Blueprint $table) {
            $table->boolean('source_cleaned')->default(false)->after('files_failed');

            $table->unsignedInteger('dest_verified_files')->default(0)->after('source_cleaned');
            $table->unsignedBigInteger('dest_verified_bytes')->default(0)->after('dest_verified_files');
        });
    }

    public function down(): void
    {
        Schema::table('agent_server_transfers', function (Blueprint $table) {
            $table->dropColumn(['source_cleaned', 'dest_verified_files', 'dest_verified_bytes']);
        });
    }
};
