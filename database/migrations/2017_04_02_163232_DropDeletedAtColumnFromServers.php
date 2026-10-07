<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropDeletedAtColumnFromServers extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('deleted_at');
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->timestamp('deleted_at')->nullable();
        });
    }
}
