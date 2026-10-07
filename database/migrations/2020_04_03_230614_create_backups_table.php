<?php

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateBackupsTable extends Migration
{
    public function up()
    {
        $db = config('database.default');
        $results = DB::select('SELECT TABLE_NAME FROM information_schema.tables WHERE table_schema = ? AND table_name LIKE ? AND table_name NOT LIKE \'%_plugin_bak\'', [
            config("database.connections.{$db}.database"),
            'backup%',
        ]);

        foreach ($results as $result) {
            Schema::rename($result->TABLE_NAME, $result->TABLE_NAME . '_plugin_bak');
        }

        Schema::create('backups', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('server_id');
            $table->char('uuid', 36);
            $table->string('name');
            $table->text('ignored_files');
            $table->string('disk');
            $table->string('sha256_hash')->nullable();
            $table->integer('bytes')->default(0);
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
            $table->softDeletes();

            $table->unique('uuid');
            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('backups');
    }
}
