<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abuse_signals', function (Blueprint $table): void {
            $table->id();
            $table->string('signal_type', 100);
            $table->string('severity', 20)->default('medium');
            $table->string('source_module', 80);
            $table->string('target_reference')->nullable();
            $table->foreignId('actor_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('ip_hash', 64)->nullable();
            $table->unsignedInteger('occurrence_count')->default(1);
            $table->timestamp('first_seen_at');
            $table->timestamp('last_seen_at');
            $table->string('status', 20)->default('open');
            $table->json('metadata')->nullable();
            $table->string('deduplication_key', 64)->index();
            $table->foreignId('reviewed_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable();
            $table->timestamps();

            $table->index(['status', 'severity', 'last_seen_at']);
            $table->index(['signal_type', 'source_module']);
            $table->index(['actor_user_id', 'last_seen_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abuse_signals');
    }
};
