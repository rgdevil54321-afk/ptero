<?php

/*
 * Turtle Game Panel - "Pure local node" (native runtime) tables.
 *
 * These servers do NOT use Wings or Docker. The panel spawns the game process
 * natively on the panel host and talks to it over a FIFO (console input) and a
 * log file (console output). This mirrors how the JTG panel's "local" runtime
 * works, without any container layer.
 *
 * The migration is additive and idempotent: it only creates a table that does
 * not already exist and never touches anything used by the panel or the theme.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('local_servers')) {
            return;
        }

        Schema::create('local_servers', function (Blueprint $table) {
            $table->id();
            $table->char('uuid', 36)->unique();
            $table->string('name', 191);
            $table->string('type', 32)->default('paper');       // paper | purpur | vanilla | custom
            $table->string('mc_version', 32)->nullable();

            $table->unsignedInteger('memory_mb')->default(2048);
            $table->unsignedInteger('port');
            $table->string('jar', 191)->default('server.jar');
            $table->string('java_bin', 255)->nullable();
            $table->string('motd', 191)->nullable();

            // new | installing | installed | starting | running | stopping | stopped | error
            $table->string('status', 32)->default('new');
            $table->text('last_error')->nullable();
            $table->boolean('desired_running')->default(false);
            $table->unsignedInteger('pid')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('installed_at')->nullable();

            // playit.gg
            $table->boolean('playit')->default(false);
            $table->unsignedInteger('playit_agent_id')->nullable();
            $table->string('playit_address', 191)->nullable();
            $table->string('playit_host', 191)->nullable();
            $table->unsignedInteger('playit_port')->nullable();
            $table->timestamp('playit_synced_at')->nullable();

            $table->unsignedInteger('created_by')->nullable();
            $table->timestamps();

            $table->index('status');
            $table->index('playit_agent_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('local_servers');
    }
};
