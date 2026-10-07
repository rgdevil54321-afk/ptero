<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('mod_collection_members')) {
            Schema::create('mod_collection_members', function (Blueprint $table) {
                $table->id();
                $table->unsignedBigInteger('mod_collection_id');
                $table->unsignedInteger('user_id');
                $table->json('permissions')->nullable();
                $table->timestamps();

                $table->foreign('mod_collection_id')->references('id')->on('mod_collections')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');

                $table->unique(['mod_collection_id', 'user_id']);
                $table->index('user_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('mod_collection_members');
    }
};
