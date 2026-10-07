<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveActiveColumn extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('active');
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->tinyInteger('active')->after('name')->unsigned()->default(0);
        });
    }
}
