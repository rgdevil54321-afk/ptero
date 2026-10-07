<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('promo_codes', function (Blueprint $table) {
            $table->enum('type', ['fixed', 'percent'])->default('fixed')->after('code');
            $table->decimal('min_amount', 10, 2)->default(0)->after('discount');
            $table->integer('usage_count')->default(0)->after('uses_remaining');
            $table->boolean('is_one_time_per_user')->default(false)->after('is_global');
        });

        Schema::create('promo_code_usages', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('promo_code_id');
            $table->unsignedInteger('user_id');
            $table->timestamps();

            $table->foreign('promo_code_id')->references('id')->on('promo_codes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_code_usages');

        Schema::table('promo_codes', function (Blueprint $table) {
            $table->dropColumn(['type', 'min_amount', 'usage_count', 'is_one_time_per_user']);
        });
    }
};
