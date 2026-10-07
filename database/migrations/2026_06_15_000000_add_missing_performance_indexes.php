<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('user_active_sessions') && Schema::hasColumn('user_active_sessions', 'login_token')) {
            Schema::table('user_active_sessions', function (Blueprint $table) {
                $table->index('login_token', 'idx_uas_login_token');
            });
        }

        if (Schema::hasTable('user_login_history') && Schema::hasColumn('user_login_history', 'user_id')) {
            Schema::table('user_login_history', function (Blueprint $table) {
                $table->index(['user_id', 'created_at'], 'idx_ulh_user_created');
            });
        }

        if (Schema::hasTable('payments') && Schema::hasColumn('payments', 'session_id')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->index('session_id', 'idx_payments_session_id');
            });
        }

        if (Schema::hasTable('user_integrations') && Schema::hasColumn('user_integrations', 'provider')) {
            Schema::table('user_integrations', function (Blueprint $table) {
                $table->index(['provider', 'provider_id'], 'idx_ui_provider_pid');
            });
        }

        if (Schema::hasTable('node_backups') && Schema::hasColumn('node_backups', 'node_id')) {
            Schema::table('node_backups', function (Blueprint $table) {
                $table->index(['node_id', 'run_id'], 'idx_nb_node_run');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('user_active_sessions')) {
            Schema::table('user_active_sessions', function (Blueprint $table) {
                $table->dropIndex('idx_uas_login_token');
            });
        }

        if (Schema::hasTable('user_login_history')) {
            Schema::table('user_login_history', function (Blueprint $table) {
                $table->dropIndex('idx_ulh_user_created');
            });
        }

        if (Schema::hasTable('payments')) {
            Schema::table('payments', function (Blueprint $table) {
                $table->dropIndex('idx_payments_session_id');
            });
        }

        if (Schema::hasTable('user_integrations')) {
            Schema::table('user_integrations', function (Blueprint $table) {
                $table->dropIndex('idx_ui_provider_pid');
            });
        }

        if (Schema::hasTable('node_backups')) {
            Schema::table('node_backups', function (Blueprint $table) {
                $table->dropIndex('idx_nb_node_run');
            });
        }
    }
};
