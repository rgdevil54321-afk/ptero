<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddMaintenanceToNodes extends Migration
{
    public function up()
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->boolean('maintenance_mode')->after('behind_proxy')->default(false);
        });
    }

    public function down()
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn('maintenance_mode');
        });
    }
}
