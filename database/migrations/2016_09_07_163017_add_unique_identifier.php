<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUniqueIdentifier extends Migration
{
    public function up()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->char('author', 36)->after('id');
        });
    }

    public function down()
    {
        Schema::table('services', function (Blueprint $table) {
            $table->dropColumn('author');
        });
    }
}
