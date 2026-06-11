<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('locales', function (Blueprint $table): void {
            $table->id();
            $table->string('language_name');
            $table->string('native_name');
            $table->string('locale', 16)->unique();
            $table->string('direction', 3)->default('ltr');
            $table->string('region', 80);
            $table->string('market_readiness', 24)->default('planned');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('launch_status', 24)->default('draft');
            $table->json('readiness')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'is_default']);
            $table->index(['launch_status', 'market_readiness']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('locales');
    }
};
