<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDockerImageColumn extends Migration
{
    public function up()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->string('image')->after('daemonSecret');
        });

        DB::transaction(function () {
            $servers = DB::table('servers')->select(
                'servers.id',
                'service_options.docker_image as s_optionImage'
            )->join('service_options', 'service_options.id', '=', 'servers.option')->get();

            foreach ($servers as $server) {
                $server->image = $server->s_optionImage;
                $server->save();
            }
        });
    }

    public function down()
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('image');
        });
    }
}
