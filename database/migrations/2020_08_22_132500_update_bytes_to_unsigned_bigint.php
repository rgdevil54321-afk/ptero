<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateBytesToUnsignedBigint extends Migration
{
    public function up()
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->unsignedBigInteger('bytes')->default(0)->change();
        });
    }

    public function down()
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->integer('bytes')->default(0)->change();
        });
    }
}
