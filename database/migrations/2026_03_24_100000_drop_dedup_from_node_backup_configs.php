<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('node_backup_configs', function (Blueprint $table) {
            if (Schema::hasColumn('node_backup_configs', 'dedup_node_backups')) {
                $table->dropColumn('dedup_node_backups');
            }
            if (Schema::hasColumn('node_backup_configs', 'dedup_native_backups')) {
                $table->dropColumn('dedup_native_backups');
            }
        });
    }

    public function down(): void
    {
        Schema::table('node_backup_configs', function (Blueprint $table) {
            $table->boolean('dedup_node_backups')->default(false)->after('default_global_backend_id');
            $table->boolean('dedup_native_backups')->default(false)->after('dedup_node_backups');
        });
    }
};
