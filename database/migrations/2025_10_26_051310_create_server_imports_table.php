<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('server_imports')) {
            Schema::create('server_imports', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('server_id');
                $table->enum('protocol', ['FTP', 'SFTP']);
                $table->string('host');
                $table->unsignedInteger('port');
                $table->string('username');
                $table->text('password');
                $table->string('remote_path')->default('/');
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');

                $table->index(['user_id', 'server_id']);
            });
        }

        Schema::table('servers', function (Blueprint $table) {
            if (!Schema::hasColumn('servers', 'meta')) {
                $table->json('meta')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('server_imports');

        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('meta');
        });
    }
};
