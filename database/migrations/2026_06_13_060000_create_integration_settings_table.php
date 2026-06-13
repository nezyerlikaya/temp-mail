<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('integration_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('integration_key');
            $table->string('environment')->default('sandbox');
            $table->boolean('is_active')->default(false);
            $table->string('connection_status')->default('not_tested');
            $table->json('payload')->nullable();
            $table->text('encrypted_secrets')->nullable();
            $table->json('test_history')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['integration_key', 'environment']);
            $table->index(['integration_key', 'is_active']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('integration_settings');
    }
};
