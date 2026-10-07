<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddNotesColumnForAllocations extends Migration
{
    public function up()
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->string('notes')->nullable()->after('server_id');
        });
    }

    public function down()
    {
        Schema::table('allocations', function (Blueprint $table) {
            $table->dropColumn('notes');
        });
    }
}
