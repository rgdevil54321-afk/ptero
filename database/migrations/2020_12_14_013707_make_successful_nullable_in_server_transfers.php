<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MakeSuccessfulNullableInServerTransfers extends Migration
{
    public function up()
    {
        Schema::table('server_transfers', function (Blueprint $table) {
            $table->boolean('successful')->nullable()->default(null)->change();
        });
    }

    public function down()
    {
        Schema::table('server_transfers', function (Blueprint $table) {
            $table->boolean('successful')->default(0)->change();
        });
    }
}
