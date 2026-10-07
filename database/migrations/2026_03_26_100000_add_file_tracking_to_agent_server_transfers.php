<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('agent_server_transfers', function (Blueprint $table) {
            $table->unsignedInteger('total_files')->default(0)->after('chunks_completed');
            $table->unsignedInteger('files_completed')->default(0)->after('total_files');
            $table->unsignedInteger('files_failed')->default(0)->after('files_completed');
            $table->string('current_file', 255)->nullable()->after('files_failed');
        });
    }

    public function down(): void
    {
        Schema::table('agent_server_transfers', function (Blueprint $table) {
            $table->dropColumn(['total_files', 'files_completed', 'files_failed', 'current_file']);
        });
    }
};
