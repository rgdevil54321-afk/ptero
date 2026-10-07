<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mods')) {
            Schema::create('mods', function (Blueprint $table) {
                $table->string('id', 50)->primary();
                $table->string('name', 255);
                $table->text('summary')->nullable();
                $table->string('author_username', 100)->nullable();
                $table->string('current_version_number', 50)->nullable();
                $table->bigInteger('current_version_size')->unsigned()->nullable();
                $table->timestamp('created_at')->nullable();
                $table->timestamp('updated_at')->nullable();
                $table->timestamp('cached_at')->nullable();

                $table->index('cached_at');
            });
        }

        if (!Schema::hasTable('mod_previews')) {
            Schema::create('mod_previews', function (Blueprint $table) {
                $table->id();
                $table->string('mod_id', 50);
                $table->text('url');
                $table->integer('preview_order')->default(0);

                $table->foreign('mod_id')->references('id')->on('mods')->onDelete('cascade');
                $table->index(['mod_id', 'preview_order']);
            });
        }

        if (!Schema::hasTable('mod_versions')) {
            Schema::create('mod_versions', function (Blueprint $table) {
                $table->id();
                $table->string('mod_id', 50);
                $table->string('version_number', 50);
                $table->bigInteger('version_size')->unsigned()->nullable();
                $table->timestamp('release_date')->nullable();
                $table->boolean('is_current')->default(false);
                $table->integer('version_order')->default(0);

                $table->foreign('mod_id')->references('id')->on('mods')->onDelete('cascade');
                $table->index(['mod_id', 'version_order']);
            });
        }

        if (!Schema::hasTable('mod_dependencies')) {
            Schema::create('mod_dependencies', function (Blueprint $table) {
                $table->id();
                $table->string('mod_id', 50);
                $table->string('dependency_id', 50);
                $table->string('dependency_name', 255)->nullable();
                $table->timestamp('cached_at')->nullable();

                $table->foreign('mod_id')->references('id')->on('mods')->onDelete('cascade');
                $table->index(['mod_id', 'cached_at']);
                $table->index('dependency_id');
                $table->unique(['mod_id', 'dependency_id']);
            });
        }

        if (!Schema::hasTable('mod_collections')) {
            Schema::create('mod_collections', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('server_id');
                $table->unsignedInteger('user_id');
                $table->string('name', 24);
                $table->string('description', 120);
                $table->string('config_name', 255);
                $table->json('mods');
                $table->timestamps();

                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->index(['server_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mod_collections');
        Schema::dropIfExists('mod_dependencies');
        Schema::dropIfExists('mod_versions');
        Schema::dropIfExists('mod_previews');
        Schema::dropIfExists('mods');
    }
};
