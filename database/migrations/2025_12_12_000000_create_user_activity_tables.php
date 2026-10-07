<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        $dropColumns = [];
        if (Schema::hasColumn('users', 'last_seen_at')) {
            $dropColumns[] = 'last_seen_at';
        }
        if (Schema::hasColumn('users', 'last_seen_ip')) {
            $dropColumns[] = 'last_seen_ip';
        }
        if (!empty($dropColumns)) {
            Schema::table('users', function (Blueprint $table) use ($dropColumns) {
                $table->dropColumn($dropColumns);
            });
        }
        if (!Schema::hasTable('user_login_history')) {
            Schema::create('user_login_history', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->string('ip_address');
                $table->text('user_agent')->nullable();
                $table->string('device_type')->default('Desktop');
                $table->string('platform')->default('Unknown');
                $table->string('browser')->default('Unknown');
                $table->boolean('is_vpn')->default(false);
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->timestamp('created_at')->useCurrent();
                
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }

        if (!Schema::hasTable('user_active_sessions')) {
            Schema::create('user_active_sessions', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->string('session_id')->unique();
                $table->string('login_token', 64)->nullable()->index();
                $table->string('ip_address');
                $table->text('user_agent')->nullable();
                $table->string('device_type')->default('Desktop');
                $table->string('platform')->default('Unknown');
                $table->string('browser')->default('Unknown');
                $table->boolean('is_vpn')->default(false);
                $table->string('city')->nullable();
                $table->string('state')->nullable();
                $table->string('country')->nullable();
                $table->boolean('is_revoked')->default(false);
                $table->timestamp('last_active_at')->useCurrent();
                $table->timestamp('created_at')->useCurrent();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    public function down()
    {
        Schema::dropIfExists('user_active_sessions');
        Schema::dropIfExists('user_login_history');
    }
};
