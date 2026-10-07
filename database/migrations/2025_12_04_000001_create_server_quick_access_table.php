<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('server_quick_access')) {
            Schema::create('server_quick_access', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('server_id');
                $table->string('name', 255);
                $table->string('path', 1000);
                $table->string('directory', 1000);
                $table->boolean('is_file')->default(true);
                $table->timestamps();

                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
                
                $table->unique(['server_id', 'path'], 'server_quick_access_unique');
                
                $table->index(['server_id', 'created_at']);
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('server_quick_access');
    }
};
