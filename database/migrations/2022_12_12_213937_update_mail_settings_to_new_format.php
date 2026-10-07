<?php

use Pterodactyl\Models\Setting;
use Illuminate\Support\Facades\DB;
use Illuminate\Database\Migrations\Migration;

return new class () extends Migration {
    private array $keys = [
        ['mail:host', 'mail:mailers:smtp:host'],
        ['mail:port', 'mail:mailers:smtp:port'],
        ['mail:encryption', 'mail:mailers:smtp:encryption'],
        ['mail:username', 'mail:mailers:smtp:username'],
        ['mail:password', 'mail:mailers:smtp:password'],
    ];

    public function up(): void
    {
        $settings = Setting::all();

        $oldKeys = array_column($this->keys, 0);
        $oldSettings = $settings->filter(fn (Setting $setting) => in_array($setting->key, $oldKeys));

        $newKeys = array_column($this->keys, 1);
        $newSettings = $settings->filter(fn (Setting $setting) => in_array($setting->key, $newKeys));

        $oldSettings->map(function (Setting $setting) use ($oldKeys) {
            $row = array_search($setting->key, $oldKeys, true);
            $setting->key = $this->keys[$row][1];

            return $setting;
        })->filter(function (Setting $setting) use ($newSettings) {
            if ($newSettings->contains('key', $setting->key)) {
                return false;
            }

            return true;
        })->each(fn (Setting $setting) => $setting->save());
    }

    public function down(): void
    {
        DB::transaction(function () {
            $settings = Setting::all();

            $newKeys = array_column($this->keys, 0);
            $newSettings = $settings->filter(fn (Setting $setting) => in_array($setting->key, $newKeys));

            $newSettings->each(fn (Setting $setting) => $setting->delete());

            $oldKeys = array_column($this->keys, 1);
            $oldSettings = $settings->filter(fn (Setting $setting) => in_array($setting->key, $oldKeys));

            $oldSettings->map(function (Setting $setting) use ($oldKeys) {
                $row = array_search($setting->key, $oldKeys, true);
                $setting->key = $this->keys[$row][0];

                return $setting;
            })->each(fn (Setting $setting) => $setting->save());
        });
    }
};
