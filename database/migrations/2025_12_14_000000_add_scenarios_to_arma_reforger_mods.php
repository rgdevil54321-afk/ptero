<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mods')) {
            Schema::table('mods', function (Blueprint $table) {
                if (!Schema::hasColumn('mods', 'scenarios')) {
                    $table->json('scenarios')->nullable()->after('current_version_size');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mods')) {
            Schema::table('mods', function (Blueprint $table) {
                if (Schema::hasColumn('mods', 'scenarios')) {
                    $table->dropColumn('scenarios');
                }
            });
        }
    }
};
