<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddAccountSuspensionToUsers extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('is_banned')->default(false)->after('root_admin');
            $table->text('ban_reason')->nullable()->after('is_banned');
            $table->timestamp('suspended_until')->nullable()->after('ban_reason');
            $table->text('suspension_reason')->nullable()->after('suspended_until');
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['is_banned', 'ban_reason', 'suspended_until', 'suspension_reason']);
        });
    }
}
