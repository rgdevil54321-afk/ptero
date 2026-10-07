<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class SetAllocationLimitDefaultNull extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->unsignedInteger('allocation_limit')->nullable()->default(null)->change();
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->unsignedInteger('allocation_limit')->nullable()->default(0)->change();
        });
    }
}
