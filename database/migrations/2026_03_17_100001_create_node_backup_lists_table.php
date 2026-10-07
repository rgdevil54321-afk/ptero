<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    public function up(): void
    {
        Schema::create('node_backup_lists', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('node_id');
            $table->unsignedInteger('server_id');
            $table->string('list_type', 10);
            $table->timestamp('created_at')->useCurrent();

            $table->unique(['node_id', 'server_id']);
            $table->foreign('node_id')->references('id')->on('nodes')->onDelete('cascade');
            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('node_backup_lists');
    }
};
