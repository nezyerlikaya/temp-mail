<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('pages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('locale_id')->constrained('locales')->cascadeOnDelete();
            $table->string('title');
            $table->string('slug');
            $table->text('excerpt')->nullable();
            $table->longText('content')->nullable();
            $table->string('content_readiness', 32)->default('outline');
            $table->foreignId('featured_media_id')->nullable()->constrained('media_assets')->nullOnDelete();
            $table->string('page_type', 48);
            $table->string('status', 32)->default('draft');
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('published_at')->nullable();
            $table->timestamp('trashed_at')->nullable();
            $table->timestamps();

            $table->unique(['locale_id', 'slug']);
            $table->index(['page_type', 'status']);
            $table->index(['author_id', 'created_at']);
            $table->index('published_at');
            $table->index('trashed_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('pages');
    }
};
