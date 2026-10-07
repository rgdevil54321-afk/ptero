<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('rolex_permission_roles')) {
            Schema::create('rolex_permission_roles', function (Blueprint $table) {
                $table->id();
                $table->string('name')->unique();
                $table->string('color')->default('#3b82f6');
                $table->json('permissions')->nullable();
                $table->timestamps();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'permission_role_id')) {
                $table->unsignedBigInteger('permission_role_id')->nullable()->after('root_admin');
                $table->foreign('permission_role_id')->references('id')->on('rolex_permission_roles')->nullOnDelete();
            }
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['permission_role_id']);
            $table->dropColumn('permission_role_id');
        });

        Schema::dropIfExists('rolex_permission_roles');
    }
};
