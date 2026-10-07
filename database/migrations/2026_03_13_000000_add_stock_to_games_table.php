<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('games', function (Blueprint $blueprint) {
            $blueprint->integer('stock')->nullable()->after('proxy_limit');
        });
    }

    public function down(): void
    {
        Schema::table('games', function (Blueprint $blueprint) {
            $blueprint->dropColumn('stock');
        });
    }
};
