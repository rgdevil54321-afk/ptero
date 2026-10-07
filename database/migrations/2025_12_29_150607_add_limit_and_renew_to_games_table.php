<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->boolean('is_free')->default(false);
            $table->integer('limit_per_user')->default(0);
            $table->boolean('auto_renew_enabled')->default(true);
        });
    }

    public function down()
    {
        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['is_free', 'limit_per_user', 'auto_renew_enabled']);
        });
    }
};
