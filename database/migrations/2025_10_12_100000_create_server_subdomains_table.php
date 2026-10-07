<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('server_subdomains')) {
            Schema::create('server_subdomains', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('server_id');
                $table->unsignedInteger('user_id');
                $table->string('subdomain');
                $table->string('domain');
                $table->string('allocation_ip');
                $table->unsignedInteger('allocation_port');
                $table->enum('game_type', ['minecraft_java', 'minecraft_bedrock'])->nullable();
                $table->longText('cloudflare_record')->nullable();
                $table->longText('srv_record')->nullable();
                $table->timestamps();

                $table->foreign('server_id')->references('id')->on('servers')->onDelete('cascade');
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
                $table->unique(['server_id', 'allocation_ip', 'allocation_port'], 'unique_allocation_subdomain');
                $table->unique(['subdomain', 'domain'], 'unique_subdomain_domain');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('server_subdomains');
    }
};