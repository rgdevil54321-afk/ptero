<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('staff_requests')) {
            Schema::create('staff_requests', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('staff_user_id');
                $table->unsignedInteger('server_id');
                $table->text('description');
                $table->boolean('urgent')->default(false);
                $table->enum('status', ['pending', 'accepted', 'rejected', 'auto_rejected'])->default('pending');
                $table->timestamp('expires_at')->nullable();
                $table->timestamps();

                $table->foreign('staff_user_id')->references('id')->on('users')->onDelete('cascade');
                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');

                $table->index(['staff_user_id', 'status']);
                $table->index(['server_id', 'status']);
                $table->index('expires_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('staff_requests');
    }
};
