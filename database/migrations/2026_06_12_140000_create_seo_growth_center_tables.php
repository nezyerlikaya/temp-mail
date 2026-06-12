<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_records', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('locale_id')->constrained('locales')->cascadeOnDelete();
            $table->string('target_type', 64);
            $table->string('target_key', 160);
            $table->string('targetable_type')->nullable();
            $table->unsignedBigInteger('targetable_id')->nullable();
            $table->string('meta_title', 180)->nullable();
            $table->text('meta_description')->nullable();
            $table->string('canonical_url', 500)->nullable();
            $table->boolean('robots_index')->default(true);
            $table->boolean('robots_follow')->default(true);
            $table->boolean('include_in_sitemap')->default(true);
            $table->decimal('sitemap_priority', 2, 1)->default(0.5);
            $table->string('sitemap_change_frequency', 32)->default('weekly');
            $table->string('og_title', 180)->nullable();
            $table->text('og_description')->nullable();
            $table->foreignId('og_image_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('twitter_card', 32)->default('summary_large_image');
            $table->string('schema_type', 64)->nullable();
            $table->json('schema_json')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['locale_id', 'target_type', 'target_key']);
            $table->index(['target_type', 'target_key']);
            $table->index(['targetable_type', 'targetable_id']);
            $table->index(['robots_index', 'include_in_sitemap']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_records');
    }
};
