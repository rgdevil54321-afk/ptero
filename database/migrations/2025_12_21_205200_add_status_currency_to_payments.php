<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->string('status')->default('pending')->after('amount');
            $table->string('currency')->default('USD')->after('amount');
        });

        DB::table('payments')->where('completed', true)->update(['status' => 'completed']);
        DB::table('payments')->where('completed', false)->update(['status' => 'pending']);
    }

    public function down(): void
    {
        Schema::table('payments', function (Blueprint $table) {
            $table->dropColumn(['status', 'currency']);
        });
    }
};
