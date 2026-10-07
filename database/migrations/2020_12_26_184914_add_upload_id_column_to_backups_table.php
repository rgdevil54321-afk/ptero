<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddUploadIdColumnToBackupsTable extends Migration
{
    public function up()
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->text('upload_id')->nullable()->after('uuid');
        });
    }

    public function down()
    {
        Schema::table('backups', function (Blueprint $table) {
            $table->dropColumn('upload_id');
        });
    }
}
