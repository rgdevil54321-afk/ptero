<?php

use Illuminate\Database\Migrations\Migration;

class RenameDoubleInsurgency extends Migration
{
    public function up()
    {
        DB::transaction(function () {
            $model = DB::table('service_options')->where('parent_service', 2)->where('id', 3)->where('name', 'Insurgency')->first();
            if ($model) {
                $model->name = 'Team Fortress 2';
                $model->save();
            }
        });
    }

    public function down()
    {
    }
}
