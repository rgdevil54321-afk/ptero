<?php

use Illuminate\Database\Migrations\Migration;
use Pterodactyl\Services\HyperV2LegacySettingsMigrator;

return new class extends Migration
{
    public function up(): void
    {
        app(HyperV2LegacySettingsMigrator::class)->migrate();
    }

    public function down(): void
    {
        // Non-destructive data migration; no rollback.
    }
};
