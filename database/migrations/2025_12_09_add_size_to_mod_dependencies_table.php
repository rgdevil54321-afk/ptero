<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('mod_dependencies')) {
            Schema::table('mod_dependencies', function (Blueprint $table) {
                if (!Schema::hasColumn('mod_dependencies', 'file_size')) {
                    $table->bigInteger('file_size')->unsigned()->default(0)->after('dependency_name');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('mod_dependencies')) {
            Schema::table('mod_dependencies', function (Blueprint $table) {
                if (Schema::hasColumn('mod_dependencies', 'file_size')) {
                    $table->dropColumn('file_size');
                }
            });
        }
    }
};
