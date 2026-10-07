<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveNullableFromScheduleNameField extends Migration
{
    public function up()
    {
        DB::update("UPDATE schedules SET name = 'Schedule' WHERE name IS NULL OR name = ''");

        Schema::table('schedules', function (Blueprint $table) {
            $table->string('name')->nullable(false)->change();
        });
    }

    public function down()
    {
        Schema::table('schedules', function (Blueprint $table) {
            $table->string('name')->nullable()->change();
        });
    }
}
