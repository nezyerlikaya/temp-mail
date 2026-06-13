<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('font_families', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('name');
            $table->string('css_family');
            $table->string('provider');
            $table->string('category');
            $table->json('supported_scripts');
            $table->boolean('rtl_support')->default(false);
            $table->json('available_weights');
            $table->string('font_display')->default('swap');
            $table->boolean('is_active')->default(true);
            $table->boolean('local_file_ready')->default(false);
            $table->boolean('media_ready')->default(false);
            $table->json('metadata')->nullable();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('font_assignments', function (Blueprint $table): void {
            $table->id();
            $table->string('scope');
            $table->string('scope_key')->default('default');
            $table->string('usage');
            $table->string('font_family_slug');
            $table->json('fallback_stack');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['scope', 'scope_key', 'usage']);
            $table->index(['scope', 'scope_key']);
            $table->foreign('font_family_slug')->references('slug')->on('font_families')->cascadeOnUpdate()->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('font_assignments');
        Schema::dropIfExists('font_families');
    }
};
