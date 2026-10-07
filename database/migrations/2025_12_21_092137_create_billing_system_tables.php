<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('game_category')) {
            Schema::create('game_category', function (Blueprint $table) {
                $table->id();
                $table->string('title');
                $table->text('image_url')->nullable();
                $table->string('short_url')->unique();
                $table->integer('hide')->default(0);
                $table->integer('sort')->default(0);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('games')) {
            Schema::create('games', function (Blueprint $table) {
                $table->id();
                $table->string('name');
                $table->foreignId('category_id')->constrained('game_category')->cascadeOnDelete();
                $table->decimal('price', 15, 2);
                $table->integer('egg_id');
                $table->text('node_ids')->nullable();
                $table->integer('cpu')->default(100);
                $table->integer('memory')->default(1024);
                $table->integer('disk')->default(1024);
                $table->integer('swap')->default(0);
                $table->integer('database_limit')->default(0);
                $table->integer('backup_limit')->default(0);
                $table->integer('allocation_limit')->default(1);
                $table->timestamps();
            });
        }

        if (!Schema::hasTable('payments')) {
            Schema::create('payments', function (Blueprint $table) {
                $table->id();
                $table->string('payment_type');
                $table->integer('user_id')->unsigned();
                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->decimal('amount', 15, 2);
                $table->string('invoice_number')->unique()->nullable();
                $table->string('session_id')->nullable()->index();
                $table->integer('completed')->default(0);
                $table->timestamps();
            });
        }

        Schema::table('users', function (Blueprint $table) {
            if (!Schema::hasColumn('users', 'credit')) {
                $table->decimal('credit', 15, 2)->default(0.00)->after('email');
            }
        });

        Schema::table('servers', function (Blueprint $table) {
            if (!Schema::hasColumn('servers', 'product_id')) {
                $table->foreignId('product_id')->nullable()->constrained('games')->nullOnDelete()->after('node_id');
            }
            if (!Schema::hasColumn('servers', 'expires_at')) {
                $table->timestamp('expires_at')->nullable()->after('product_id');
            }
        });
    }

    public function down(): void
    {
        Schema::table('servers', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropColumn(['product_id', 'expires_at']);
        });

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('credit');
        });

        Schema::dropIfExists('payments');
        Schema::dropIfExists('games');
        Schema::dropIfExists('game_category');
    }
};
