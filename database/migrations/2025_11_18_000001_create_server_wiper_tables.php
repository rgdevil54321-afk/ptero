<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('wipe_schedules')) {
            Schema::create('wipe_schedules', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('server_id');
                $table->string('name');
                $table->text('description')->nullable();
                $table->boolean('is_active')->default(true);
                $table->boolean('stop_server')->default(false);
                $table->string('new_server_name')->nullable();
                $table->json('file_patterns')->nullable();
                $table->json('commands')->nullable();
                $table->string('timezone', 50)->default('UTC');
                $table->enum('schedule_type', ['single', 'recurring'])->default('single');
                $table->json('recurrence_config')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->timestamp('last_run_at')->nullable();
                $table->json('rust_config')->nullable();
                $table->json('meta')->nullable();
                $table->timestamps();

                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
                $table->index(['server_id', 'is_active']);
                $table->index('next_run_at');
            });
        }

        if (!Schema::hasTable('wipe_executions')) {
            Schema::create('wipe_executions', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('schedule_id')->nullable();
                $table->unsignedInteger('server_id');
                $table->enum('status', ['running', 'completed', 'failed'])->default('running');
                $table->text('error_message')->nullable();
                $table->json('deleted_files')->nullable();
                $table->integer('files_deleted_count')->default(0);
                $table->boolean('server_was_stopped')->default(false);
                $table->boolean('server_was_started')->default(false);
                $table->json('commands_executed')->nullable();
                $table->json('meta')->nullable();
                $table->timestamp('started_at');
                $table->timestamp('completed_at')->nullable();
                $table->timestamps();

                $table->foreign('schedule_id')->references('id')->on('wipe_schedules')->onDelete('set null');
                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
                $table->index(['server_id', 'status']);
                $table->index('started_at');
            });
        }

        if (!Schema::hasTable('rust_map_library')) {
            Schema::create('rust_map_library', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('server_id');
                $table->string('name');
                $table->string('map_url', 1000);
                $table->integer('map_size')->nullable();
                $table->string('map_seed', 100)->nullable();
                $table->string('description', 500)->nullable();
                $table->timestamps();

                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
                $table->index('server_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('rust_map_library');
        Schema::dropIfExists('wipe_executions');
        Schema::dropIfExists('wipe_schedules');
    }
};
