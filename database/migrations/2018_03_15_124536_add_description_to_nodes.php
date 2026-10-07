<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDescriptionToNodes extends Migration
{
    public function up()
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->text('description')->after('name');
        });
    }

    public function down()
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn('description');
        });
    }
}
