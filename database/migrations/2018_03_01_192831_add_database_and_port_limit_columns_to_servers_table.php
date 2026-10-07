<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDatabaseAndPortLimitColumnsToServersTable extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->unsignedInteger('database_limit')->after('installed')->nullable()->default(0);
            $table->unsignedInteger('allocation_limit')->after('installed')->nullable()->default(0);
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn(['database_limit', 'allocation_limit']);
        });
    }
}
