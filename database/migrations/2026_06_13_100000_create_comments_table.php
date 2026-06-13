<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('comments', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('blog_post_id')->constrained('blog_posts')->cascadeOnDelete();
            $table->foreignId('parent_id')->nullable()->constrained('comments')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('locale_id')->constrained('locales')->cascadeOnDelete();
            $table->string('author_name', 120);
            $table->string('author_email')->nullable();
            $table->string('author_email_hash', 64)->nullable()->index();
            $table->string('ip_hash', 64)->nullable()->index();
            $table->json('user_agent_metadata')->nullable();
            $table->text('content');
            $table->text('content_excerpt')->nullable();
            $table->string('status', 24)->default('pending')->index();
            $table->unsignedTinyInteger('spam_score')->default(0)->index();
            $table->string('spam_provider', 40)->nullable();
            $table->string('provider_decision', 40)->nullable()->index();
            $table->foreignId('approved_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('trashed_at')->nullable();
            $table->timestamps();

            $table->index(['blog_post_id', 'status']);
            $table->index(['locale_id', 'status']);
            $table->index(['created_at', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comments');
    }
};
