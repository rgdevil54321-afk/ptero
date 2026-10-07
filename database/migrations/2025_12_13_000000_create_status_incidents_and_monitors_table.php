<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('status_incidents')) {
            Schema::create('status_incidents', function (Blueprint $table) {
                $table->id();
                $table->string('subject_type');
                $table->unsignedBigInteger('subject_id');
                $table->dateTime('started_at');
                $table->dateTime('resolved_at')->nullable();
                $table->text('error_message')->nullable();
                $table->timestamps();

                $table->index(['subject_type', 'subject_id']);
            });
        }

        if (!Schema::hasTable('custom_monitors')) {
            Schema::create('custom_monitors', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->enum('type', ['http', 'tcp', 'udp']);
                $table->string('target');
                $table->integer('port')->nullable();
                $table->timestamps();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('status_incidents');
        Schema::dropIfExists('custom_monitors');
    }
};
