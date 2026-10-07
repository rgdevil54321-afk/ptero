<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::table('node_backup_configs', function (Blueprint $table) {
            $table->unsignedBigInteger('default_global_backend_id')->nullable()->after('storage_backends');
        });
    }

    public function down(): void
    {
        Schema::table('node_backup_configs', function (Blueprint $table) {
            $table->dropColumn('default_global_backend_id');
        });
    }
};
