<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('server_splits')) {
            Schema::create('server_splits', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('master_server_id');
                $table->unsignedInteger('sub_server_id');
                $table->unsignedInteger('allocated_memory');
                $table->unsignedInteger('allocated_cpu');
                $table->unsignedBigInteger('allocated_disk');
                $table->unsignedInteger('allocated_network_allocations');
                $table->unsignedInteger('allocated_database_limit');
                $table->string('sub_server_name');
                $table->timestamps();

                $table->foreign('master_server_id')->references('id')->on('servers')->onDelete('cascade');
                $table->foreign('sub_server_id')->references('id')->on('servers')->onDelete('cascade');

                $table->unique(['master_server_id', 'sub_server_id']);
                $table->index(['master_server_id']);
            });
        }

        Schema::table('server_splits', function (Blueprint $table) {
            if (!Schema::hasColumn('server_splits', 'allocated_backup_limit')) {
                $table->unsignedInteger('allocated_backup_limit')->after('allocated_database_limit');
            }
        });

        Schema::table('servers', function (Blueprint $table) {
            if (!Schema::hasColumn('servers', 'masterserver')) {
                $table->string('masterserver', 36)->nullable()->after('uuid');
            }
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropColumn('masterserver');
        });

        Schema::table('server_splits', function (Blueprint $table) {
            $table->dropColumn('allocated_backup_limit');
        });

        Schema::dropIfExists('server_splits');
    }
};
