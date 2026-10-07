<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::table('node_backup_configs', function (Blueprint $table) {
            $table->boolean('dedup_node_backups')->default(false)->after('storage_backends');
            $table->boolean('dedup_native_backups')->default(false)->after('dedup_node_backups');
        });

        Schema::table('node_backups', function (Blueprint $table) {
            $table->boolean('is_deduplicated')->default(false)->after('checksum');
        });

        if (Schema::hasTable('backups')) {
            Schema::table('backups', function (Blueprint $table) {
                if (!Schema::hasColumn('backups', 'is_deduplicated')) {
                    $table->boolean('is_deduplicated')->default(false)->after('checksum');
                }
            });
        }
    }

    public function down(): void
    {
        Schema::table('node_backup_configs', function (Blueprint $table) {
            $table->dropColumn(['dedup_node_backups', 'dedup_native_backups']);
        });

        Schema::table('node_backups', function (Blueprint $table) {
            $table->dropColumn('is_deduplicated');
        });

        if (Schema::hasTable('backups') && Schema::hasColumn('backups', 'is_deduplicated')) {
            Schema::table('backups', function (Blueprint $table) {
                $table->dropColumn('is_deduplicated');
            });
        }
    }
};
