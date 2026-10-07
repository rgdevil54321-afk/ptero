<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddSubusers extends Migration
{
    public function up()
    {
        Schema::create('subusers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned();
            $table->integer('server_id')->unsigned();
            $table->char('daemonSecret', 36)->unique();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('subusers');
    }
}
