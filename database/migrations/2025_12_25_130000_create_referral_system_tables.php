<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasColumn('users', 'referrer_id')) {
             Schema::table('users', function (Blueprint $table) {
                 $table->dropColumn('referrer_id');
             });
        }
        
        if (Schema::hasColumn('users', 'referral_balance')) {
             Schema::table('users', function (Blueprint $table) {
                 $table->dropColumn('referral_balance');
             });
        }

        Schema::table('users', function (Blueprint $table) {
            $table->unsignedInteger('referrer_id')->nullable()->after('remember_token');
            $table->decimal('referral_balance', 15, 2)->default(0)->after('referrer_id');
            
            $table->foreign('referrer_id')->references('id')->on('users')->nullOnDelete();
        });

        if (!Schema::hasTable('referrals')) {
            Schema::create('referrals', function (Blueprint $table) {
                $table->id();
                $table->unsignedInteger('user_id');
                $table->unsignedInteger('referred_id')->nullable(); 
                $table->decimal('amount', 15, 2);
                $table->string('type');
                $table->json('custom_data')->nullable();
                $table->timestamps();

                $table->foreign('user_id')->references('id')->on('users')->cascadeOnDelete();
                $table->foreign('referred_id')->references('id')->on('users')->nullOnDelete();
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('referrals');

        Schema::table('users', function (Blueprint $table) {
            $table->dropForeign(['referrer_id']);
            $table->dropColumn(['referrer_id', 'referral_balance']);
        });
    }
};
