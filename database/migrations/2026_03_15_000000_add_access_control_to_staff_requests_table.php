<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('staff_requests')) {
            Schema::table('staff_requests', function (Blueprint $table) {
                if (!Schema::hasColumn('staff_requests', 'request_type')) {
                    $table->enum('request_type', ['staff_request', 'owner_request'])->default('staff_request')->after('status');
                }
                if (!Schema::hasColumn('staff_requests', 'access_level')) {
                    $table->enum('access_level', ['full', 'limited'])->default('full')->after('request_type');
                }

                if (!Schema::hasColumn('staff_requests', 'permissions')) {
                    $table->json('permissions')->nullable()->after('access_level');
                }
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('staff_requests')) {
            Schema::table('staff_requests', function (Blueprint $table) {
                if (Schema::hasColumn('staff_requests', 'permissions')) {
                    $table->dropColumn('permissions');
                }
                if (Schema::hasColumn('staff_requests', 'access_level')) {
                    $table->dropColumn('access_level');
                }
                if (Schema::hasColumn('staff_requests', 'request_type')) {
                    $table->dropColumn('request_type');
                }
            });
        }
    }
};
