<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class RemoveDefaultNullValueOnTable extends Migration
{
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('external_id')->default(null)->change();
        });

        DB::transaction(function () {
            DB::table('users')->where('external_id', '=', 'NULL')->update([
                'external_id' => null,
            ]);
        });
    }

    public function down()
    {
    }
}
