<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        try {
            if (!Schema::hasColumn('nodes', 'sftp_port_alias')) {
                Schema::table('nodes', function (Blueprint $table) {
                    $table->unsignedSmallInteger('sftp_port_alias')->nullable()->after('sftp_alias');
                });
            }
        } catch (\Exception $e) {
            if (!str_contains($e->getMessage(), 'Duplicate column name') && !str_contains($e->getMessage(), '42S21')) {
                throw $e;
            }
        }
    }

    public function down(): void
    {
        if (Schema::hasColumn('nodes', 'sftp_port_alias')) {
            Schema::table('nodes', function (Blueprint $table) {
                $table->dropColumn('sftp_port_alias');
            });
        }
    }
};
