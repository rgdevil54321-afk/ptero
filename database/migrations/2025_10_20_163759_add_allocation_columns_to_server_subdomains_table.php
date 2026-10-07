<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('server_subdomains', function (Blueprint $table) {
            if (!Schema::hasColumn('server_subdomains', 'allocation_id')) {
                $table->unsignedInteger('allocation_id')->after('domain');
                $table->foreign('allocation_id')->references('id')->on('allocations');
            }
            if (!Schema::hasColumn('server_subdomains', 'allocation_alias')) {
                $table->string('allocation_alias')->nullable()->after('allocation_port');
            }
        });
    }

    public function down(): void
    {
        Schema::table('server_subdomains', function (Blueprint $table) {
            if (Schema::hasColumn('server_subdomains', 'allocation_id')) {
                $table->dropForeign(['allocation_id']);
                $table->dropColumn('allocation_id');
            }
            if (Schema::hasColumn('server_subdomains', 'allocation_alias')) {
                $table->dropColumn('allocation_alias');
            }
        });
    }
};
