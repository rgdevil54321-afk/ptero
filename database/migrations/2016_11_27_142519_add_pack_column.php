<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddPackColumn extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->unsignedInteger('pack')->nullable()->after('option');

            $table->foreign('pack')->references('id')->on('service_packs');
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropForeign(['pack']);
            $table->dropColumn('pack');
        });
    }
}
