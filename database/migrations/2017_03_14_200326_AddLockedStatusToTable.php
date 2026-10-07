<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddLockedStatusToTable extends Migration
{
    public function up()
    {
        Schema::table('packs', function (Blueprint $table) {
            $table->boolean('locked')->default(false)->after('visible');
        });
    }

    public function down()
    {
        Schema::table('packs', function (Blueprint $table) {
            $table->dropColumn('locked');
        });
    }
}
