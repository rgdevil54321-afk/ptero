<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('admin_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('user_id')->nullable()->index();
            $table->string('username')->nullable();
            $table->string('user_email')->nullable();
            $table->string('action', 16);
            $table->string('endpoint', 1024);
            $table->string('ip_address', 64)->nullable();
            $table->text('user_agent')->nullable();
            $table->unsignedSmallInteger('response_status')->nullable();
            $table->unsignedInteger('api_key_id')->nullable()->index();
            $table->string('api_key_identifier', 16)->nullable();
            $table->string('request_type', 16)->default('admin');
            $table->timestamp('created_at')->useCurrent()->index();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('admin_audit_logs');
    }
};
