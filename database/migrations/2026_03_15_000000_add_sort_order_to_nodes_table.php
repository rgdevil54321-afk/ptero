<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

class AddSortOrderToNodesTable extends Migration
{
    public function up()
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->integer('sort')->unsigned()->default(0)->after('id');
        });

        $nodes = DB::table('nodes')->orderBy('id')->pluck('id');
        foreach ($nodes as $index => $id) {
            DB::table('nodes')->where('id', $id)->update(['sort' => $index + 1]);
        }
    }

    public function down()
    {
        Schema::table('nodes', function (Blueprint $table) {
            $table->dropColumn('sort');
        });
    }
}
