<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mod_collections', function (Blueprint $table) {
            if (!Schema::hasColumn('mod_collections', 'is_public')) {
                $table->boolean('is_public')->default(true)->after('mods');
            }
        });
    }

    public function down(): void
    {
        Schema::table('mod_collections', function (Blueprint $table) {
            $table->dropColumn('is_public');
        });
    }
};
