<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('appearance_settings', function (Blueprint $table): void {
            $table->id();
            $table->string('theme_slug')->unique();
            $table->string('mode')->default('defaults');
            $table->json('draft_tokens')->nullable();
            $table->json('published_tokens')->nullable();
            $table->timestamp('published_at')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['mode', 'theme_slug']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appearance_settings');
    }
};
