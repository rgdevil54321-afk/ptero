<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowEggVariablesToHaveLongerValues extends Migration
{
    public function up()
    {
        Schema::table('egg_variables', function (Blueprint $table) {
            $table->text('default_value')->change();
        });
    }

    public function down()
    {
        Schema::table('egg_variables', function (Blueprint $table) {
            $table->string('default_value')->change();
        });
    }
}
