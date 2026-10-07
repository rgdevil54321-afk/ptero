<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (!Schema::hasTable('user_integrations')) {
            return;
        }

        if (!Schema::hasColumn('user_integrations', 'integrity_hash')) {
            Schema::table('user_integrations', function (Blueprint $table) {
                $table->string('integrity_hash', 64)->nullable()->after('refresh_token');
            });
        }

        $appKey = (string) config('app.key');
        if ($appKey === '') {
            return;
        }

        DB::table('user_integrations')
            ->select(['id', 'user_id', 'provider', 'provider_id'])
            ->orderBy('id')
            ->chunkById(200, function ($rows) use ($appKey) {
                foreach ($rows as $row) {
                    $payload = $row->user_id . '|' . $row->provider . '|' . $row->provider_id;
                    DB::table('user_integrations')
                        ->where('id', $row->id)
                        ->update([
                            'integrity_hash' => hash_hmac('sha256', $payload, $appKey),
                        ]);
                }
            });
    }

    public function down(): void
    {
        if (Schema::hasTable('user_integrations') && Schema::hasColumn('user_integrations', 'integrity_hash')) {
            Schema::table('user_integrations', function (Blueprint $table) {
                $table->dropColumn('integrity_hash');
            });
        }
    }
};
