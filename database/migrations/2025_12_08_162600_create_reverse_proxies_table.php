<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('reverse_proxies')) {
            Schema::create('reverse_proxies', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('server_id');
                $table->unsignedInteger('allocation_id');
                $table->string('domain')->unique();
                $table->string('ssl_type')->default('none');
                $table->text('ssl_certificate')->nullable();
                $table->text('ssl_key')->nullable();
                $table->boolean('ip_verified')->default(false);
                $table->timestamps();

                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
                $table->foreign('allocation_id')->references('id')->on('allocations')->onDelete('cascade');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('reverse_proxies');
    }
};
