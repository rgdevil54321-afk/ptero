<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasColumn('users', 'referral_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->string('referral_code', 64)->nullable()->after('referral_balance')->unique();
            });
        }

        DB::table('users')->whereNull('referral_code')->orderBy('id')->chunkById(100, function ($users) {
            foreach ($users as $user) {
                $code = $user->username ?: (string) $user->id;
                if (DB::table('users')->where('referral_code', $code)->exists()) {
                    $code = $code . '-' . $user->id;
                }
                DB::table('users')->where('id', $user->id)->update(['referral_code' => $code]);
            }
        });
    }

    public function down(): void
    {
        if (Schema::hasColumn('users', 'referral_code')) {
            Schema::table('users', function (Blueprint $table) {
                $table->dropUnique(['referral_code']);
                $table->dropColumn('referral_code');
            });
        }
    }
};
