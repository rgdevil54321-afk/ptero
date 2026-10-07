<?php

use Illuminate\Database\Migrations\Migration;

class CorrectServiceVariables extends Migration
{
    public function up()
    {
        DB::transaction(function () {
            DB::table('service_options')->where([
                ['name', 'Spigot'],
                ['tag', 'spigot'],
                ['startup', '-Xms128M -Xmx{{SERVER_MEMORY}}M -Djline.terminal=jline.UnsupportedTerminal -jar {{SERVER_JARFILE}}'],
            ])->update([
                'startup' => null,
            ]);

            DB::table('service_variables')->where([
                ['name', 'Spigot Version'],
                ['env_variable', 'DL_VERSION'],
                ['default_value', 'latest'],
                ['regex', '/^(latest|[a-zA-Z0-9_\.-]{5,6})$/'],
            ])->update([
                'regex' => '/^(latest|[a-zA-Z0-9_\.-]{3,7})$/',
            ]);

            DB::table('service_variables')->where([
                ['name', 'Server Jar File'],
                ['env_variable', 'VANILLA_VERSION'],
                ['default_value', 'latest'],
                ['regex', '/^(latest|[a-zA-Z0-9_\.-]{5,6})$/'],
            ])->update([
                'name' => 'Server Version',
                'regex' => '/^(latest|[a-zA-Z0-9_\.-]{3,7})$/',
            ]);

            DB::table('service_variables')->where([
                ['name', 'Sponge Version'],
                ['env_variable', 'SPONGE_VERSION'],
                ['default_value', '1.8.9-4.2.0-BETA-351'],
                ['regex', '/^(.*)$/'],
            ])->update([
                'default_value' => '1.10.2-5.1.0-BETA-359',
                'regex' => '/^([a-zA-Z0-9.\-_]+)$/',
            ]);

            DB::table('service_variables')->where([
                ['name', 'Bungeecord Version'],
                ['env_variable', 'BUNGEE_VERSION'],
                ['default_value', 'latest'],
                ['regex', '/^(latest|[\d]{3,5})$/'],
            ])->update([
                'regex' => '/^(latest|[\d]{1,6})$/',
            ]);
        });
    }

    public function down()
    {
    }
}
