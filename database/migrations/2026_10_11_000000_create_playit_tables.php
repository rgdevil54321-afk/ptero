<?php

/*
 * Turtle Game Panel - Playit.gg integration tables.
 *
 * This migration is intentionally additive and idempotent: it only creates
 * tables that do not already exist and never touches anything used by the
 * panel or the theme.
 */

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('playit_settings')) {
            Schema::create('playit_settings', function (Blueprint $table) {
                $table->string('key', 191)->primary();
                $table->text('value')->nullable();
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('playit_agents')) {
            Schema::create('playit_agents', function (Blueprint $table) {
                $table->id();
                $table->string('label', 191)->nullable();
                // The playit "agent secret key". Treated as a secret, never logged.
                $table->text('secret_key');
                $table->string('agent_id', 191)->nullable();
                // Assignment (one agent <-> one server).
                $table->char('server_uuid', 36)->nullable()->unique();
                $table->unsignedInteger('server_id')->nullable();
                // Lifecycle: available | assigned | online | error
                $table->string('status', 32)->default('available');
                // The connectable address reported by playit (may include a port).
                $table->string('address', 191)->nullable();
                $table->string('host', 191)->nullable();
                $table->unsignedInteger('port')->nullable();
                $table->string('tunnel_id', 191)->nullable();
                $table->text('last_error')->nullable();
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();

                $table->index('status');
            });
        }

        if (!Schema::hasTable('playit_links')) {
            Schema::create('playit_links', function (Blueprint $table) {
                $table->id();
                $table->char('server_uuid', 36)->unique();
                $table->unsignedInteger('server_id')->nullable();
                $table->string('address', 191);
                $table->string('host', 191);
                $table->unsignedInteger('port')->nullable();
                $table->string('tunnel_id', 191)->nullable();
                $table->string('agent_id', 191)->nullable();
                // sync = resolved by the panel from a pool secret, agent = reported by a node.
                $table->string('source', 32)->default('sync');
                $table->timestamp('last_synced_at')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('playit_links');
        Schema::dropIfExists('playit_agents');
        Schema::dropIfExists('playit_settings');
    }
};
