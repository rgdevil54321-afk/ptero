<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('agent_server_transfers', function (Blueprint $table) {
            $table->string('phase', 30)->default('pending')->after('status');
        });
    }

    public function down(): void
    {
        Schema::table('agent_server_transfers', function (Blueprint $table) {
            $table->dropColumn('phase');
        });
    }
};
