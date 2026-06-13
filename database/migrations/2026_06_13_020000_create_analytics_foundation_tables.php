<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('analytics_events', function (Blueprint $table): void {
            $table->id();
            $table->string('event_key', 80);
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('locale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('visitor_hash', 64)->nullable();
            $table->string('session_hash', 64)->nullable();
            $table->string('ip_hash', 64)->nullable();
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->index(['event_key', 'created_at']);
            $table->index(['user_id', 'created_at']);
            $table->index(['locale_id', 'created_at']);
            $table->index(['domain_id', 'created_at']);
        });

        Schema::create('analytics_daily_metrics', function (Blueprint $table): void {
            $table->id();
            $table->date('metric_date');
            $table->string('event_key', 80);
            $table->foreignId('locale_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->unsignedInteger('total_count')->default(0);
            $table->unsignedInteger('unique_visitors')->default(0);
            $table->json('metadata')->nullable();
            $table->timestamps();

            $table->unique(['metric_date', 'event_key', 'locale_id', 'domain_id'], 'analytics_daily_unique');
            $table->index(['event_key', 'metric_date']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('analytics_daily_metrics');
        Schema::dropIfExists('analytics_events');
    }
};
