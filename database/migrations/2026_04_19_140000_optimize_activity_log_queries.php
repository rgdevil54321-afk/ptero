<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('activity_logs') && Schema::hasColumn('activity_logs', 'timestamp')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->index('timestamp', 'idx_activity_logs_timestamp');
            });
        }

        if (Schema::hasTable('activity_log_subjects')
            && Schema::hasColumn('activity_log_subjects', 'subject_type')
            && Schema::hasColumn('activity_log_subjects', 'subject_id')
            && Schema::hasColumn('activity_log_subjects', 'activity_log_id')) {
            Schema::table('activity_log_subjects', function (Blueprint $table) {
                $table->index(['subject_type', 'subject_id', 'activity_log_id'], 'idx_als_subject_activity');
            });
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('activity_logs')) {
            Schema::table('activity_logs', function (Blueprint $table) {
                $table->dropIndex('idx_activity_logs_timestamp');
            });
        }

        if (Schema::hasTable('activity_log_subjects')) {
            Schema::table('activity_log_subjects', function (Blueprint $table) {
                $table->dropIndex('idx_als_subject_activity');
            });
        }
    }
};