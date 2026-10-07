<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AllowServerVariablesToHaveLongerValues extends Migration
{
    public function up()
    {
        Schema::table('server_variables', function (Blueprint $table) {
            $table->text('variable_value')->change();
        });
    }

    public function down()
    {
        Schema::table('server_variables', function (Blueprint $table) {
            $table->string('variable_value')->change();
        });
    }
}
