<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowNegativeValuesForServerSwap extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->integer('swap')->change();
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->unsignedInteger('swap')->change();
        });
    }
}
