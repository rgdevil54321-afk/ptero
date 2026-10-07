<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddForceOutgoingIpColumnToEggsTable extends Migration
{
    public function up()
    {
        Schema::table('eggs', function (Blueprint $table) {
            $table->boolean('force_outgoing_ip')->default(false);
        });
    }

    public function down()
    {
        Schema::table('eggs', function (Blueprint $table) {
            $table->dropColumn('force_outgoing_ip');
        });
    }
}
