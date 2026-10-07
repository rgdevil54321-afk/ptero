<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class AddFivemUtilsVariablesToEgg extends Migration
{
    public function up()
    {
        $egg = DB::table('eggs')->where('name', 'like', '%FiveM%')->first();
        
        if (!$egg) {
            return;
        }

        $eggId = $egg->id;
        $now = Carbon::now();

        $variables = [
            [
                'egg_id' => $eggId,
                'name' => 'Game Build',
                'description' => 'The game build version to use.',
                'env_variable' => 'GAME_BUILD',
                'default_value' => '',
                'user_viewable' => 1,
                'user_editable' => 1,
                'rules' => 'nullable|string|max:20',
                'created_at' => $now,
                'updated_at' => $now,
            ],
            [
                'egg_id' => $eggId,
                'name' => 'MySQL Connection String',
                'description' => 'The database connection string.',
                'env_variable' => 'MYSQL_CONNECTION_STRING',
                'default_value' => '',
                'user_viewable' => 0,
                'user_editable' => 0,
                'rules' => 'nullable|string',
                'created_at' => $now,
                'updated_at' => $now,
            ]
        ];

        foreach ($variables as $variable) {
            $exists = DB::table('egg_variables')
                ->where('egg_id', $eggId)
                ->where('env_variable', $variable['env_variable'])
                ->exists();

            if (!$exists) {
                DB::table('egg_variables')->insert($variable);
            }
        }
    }

    public function down()
    {
        $egg = DB::table('eggs')->where('name', 'like', '%FiveM%')->first();
        
        if (!$egg) {
             return;
        }

        $eggId = $egg->id;
        
        DB::table('egg_variables')
            ->where('egg_id', $eggId)
            ->whereIn('env_variable', ['GAME_BUILD', 'MYSQL_CONNECTION_STRING'])
            ->delete();
    }
}
