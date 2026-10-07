<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('reverse_proxy_whitelist', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('server_id');
            $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
            $table->integer('proxy_limit')->default(0);
            $table->timestamps();
            
            $table->unique('server_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('reverse_proxy_whitelist');
    }
};
