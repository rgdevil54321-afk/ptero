<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('ddos_alert_events', function (Blueprint $table) {
            $table->id();
            $table->string('attack_hash', 64)->unique();
            $table->string('host', 128)->index();
            $table->string('status', 32)->default('unknown')->index();
            $table->string('reason', 255)->nullable();
            $table->decimal('peak_bps', 12, 2)->default(0);
            $table->unsignedBigInteger('peak_pps')->default(0);
            $table->timestamp('started_at')->nullable()->index();
            $table->timestamp('ended_at')->nullable()->index();
            $table->timestamp('first_seen_at')->nullable()->index();
            $table->timestamp('last_seen_at')->nullable()->index();
            $table->timestamp('last_notified_at')->nullable();
            $table->json('raw_payload')->nullable();
            $table->timestamps();

            $table->index(['host', 'started_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('ddos_alert_events');
    }
};
