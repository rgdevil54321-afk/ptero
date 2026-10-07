<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('server_recycle_bin')) {
            Schema::create('server_recycle_bin', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('server_id');
                $table->string('name', 255);
                $table->text('original_path');
                $table->text('original_directory');
                $table->boolean('is_file')->default(true);
                $table->bigInteger('size')->nullable();
                $table->timestamp('modified_at')->nullable();
                $table->timestamp('deleted_at');
                $table->timestamp('expires_at');
                $table->string('mimetype', 255)->nullable();
                $table->string('extension', 50)->nullable();
                $table->text('recycle_bin_path');
                $table->timestamps();

                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
                
                $table->index(['server_id', 'deleted_at']);
                $table->index(['server_id', 'expires_at']);
                $table->index('expires_at');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('server_recycle_bin');
    }
};
