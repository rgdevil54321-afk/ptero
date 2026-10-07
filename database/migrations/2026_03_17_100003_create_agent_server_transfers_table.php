<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('agent_server_transfers', function (Blueprint $table) {
            $table->id();
            $table->char('transfer_id', 36)->unique();
            $table->unsignedInteger('server_id');
            $table->unsignedInteger('source_node_id');
            $table->unsignedInteger('dest_node_id');
            $table->unsignedInteger('old_allocation_id');
            $table->unsignedInteger('new_allocation_id');
            $table->json('old_additional_allocations')->nullable();
            $table->json('new_additional_allocations')->nullable();
            $table->boolean('include_native_backups')->default(false);
            $table->string('status', 20)->default('pending');
            $table->unsignedInteger('total_chunks')->default(0);
            $table->unsignedInteger('chunks_completed')->default(0);
            $table->unsignedBigInteger('total_bytes')->default(0);
            $table->unsignedBigInteger('bytes_transferred')->default(0);
            $table->string('file_checksum', 128)->nullable();
            $table->text('transfer_token')->nullable();
            $table->text('error_message')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();

            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
            $table->foreign('source_node_id')->references('id')->on('nodes')->onDelete('cascade');
            $table->foreign('dest_node_id')->references('id')->on('nodes')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('agent_server_transfers');
    }
};
