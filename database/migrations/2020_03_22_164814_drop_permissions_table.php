<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DropPermissionsTable extends Migration
{
    public function up()
    {
        Schema::dropIfExists('permissions');
    }

    public function down()
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('subuser_id');
            $table->string('permission');

            $table->foreign('subuser_id')->references('id')->on('subusers')->onDelete('cascade');
        });
    }
}
