<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::table('settings')->insertOrIgnore([
            'key' => 'settings::app:admin_theme',
            'value' => 'default',
        ]);
    }

    public function down(): void
    {
        DB::table('settings')->where('key', 'settings::app:admin_theme')->delete();
    }
};
