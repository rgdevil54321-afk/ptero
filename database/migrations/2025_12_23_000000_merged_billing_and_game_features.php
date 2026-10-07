<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {

        if (!Schema::hasTable('promo_codes')) {
            Schema::create('promo_codes', function (Blueprint $table) {
                $table->id();
                $table->string('code')->unique();
                $table->decimal('discount', 10, 2);
                $table->integer('uses_remaining')->nullable();
                $table->timestamp('expires_at')->nullable();
                $table->json('applicable_categories')->nullable();
                $table->json('applicable_plans')->nullable();
                $table->boolean('is_global')->default(false);
                $table->timestamps();
            });
        }

        Schema::table('game_category', function (Blueprint $table) {
            if (!Schema::hasColumn('game_category', 'description')) {
                $table->text('description')->nullable()->after('title');
            }
            if (!Schema::hasColumn('game_category', 'logo_url')) {
                $table->text('logo_url')->nullable()->after('image_url');
            }
        });

        Schema::table('games', function (Blueprint $table) {
            if (!Schema::hasColumn('games', 'days')) {
                $table->integer('days')->default(30)->after('price');
            }
            if (!Schema::hasColumn('games', 'icon')) {
                $table->string('icon')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('promo_codes');

        Schema::table('game_category', function (Blueprint $table) {
            $table->dropColumn(['description', 'logo_url']);
        });

        Schema::table('games', function (Blueprint $table) {
            $table->dropColumn(['days', 'icon']);
        });
    }
};
