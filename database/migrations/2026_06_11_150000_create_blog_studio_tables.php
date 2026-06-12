<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blog_categories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('locale_id')->constrained('locales')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->unique(['locale_id', 'slug']);
            $table->index(['locale_id', 'is_active']);
        });

        Schema::create('blog_tags', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('locale_id')->constrained('locales')->cascadeOnDelete();
            $table->string('name');
            $table->string('slug');
            $table->timestamps();

            $table->unique(['locale_id', 'slug']);
        });

        Schema::create('blog_posts', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('locale_id')->constrained('locales')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('content_readiness', 32)->default('outline');
            $table->foreignId('featured_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->foreignId('blog_category_id')->nullable()->constrained('blog_categories')->nullOnDelete();
            $table->string('status', 32)->default('draft');
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('trashed_at')->nullable();
            $table->string('preview_token', 80)->nullable()->unique();
            $table->timestamps();

            $table->unique(['locale_id', 'slug']);
            $table->index(['locale_id', 'status']);
            $table->index(['blog_category_id', 'status']);
            $table->index(['author_id', 'created_at']);
            $table->index('published_at');
            $table->index('trashed_at');
        });

        Schema::create('blog_post_tag', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('blog_tag_id')->constrained('blog_tags')->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['blog_post_id', 'blog_tag_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blog_post_tag');
        Schema::dropIfExists('blog_posts');
        Schema::dropIfExists('blog_tags');
        Schema::dropIfExists('blog_categories');
    }
};
