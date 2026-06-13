<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('comments', function (Blueprint $table): void {
            $table->foreignId('edited_by')->nullable()->after('approved_at')->constrained('users')->nullOnDelete();
            $table->timestamp('edited_at')->nullable()->after('edited_by');
            $table->string('manual_override')->nullable()->after('provider_decision');
            $table->string('original_provider_decision')->nullable()->after('manual_override');
            $table->unsignedSmallInteger('reply_depth')->default(0)->after('parent_id')->index();
        });

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->boolean('comments_enabled')->default(true)->after('preview_token');
            $table->timestamp('comments_closed_at')->nullable()->after('comments_enabled');
            $table->boolean('comments_moderation_required')->default(true)->after('comments_closed_at');
        });

        Schema::create('comment_edit_histories', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('comment_id')->constrained('comments')->cascadeOnDelete();
            $table->foreignId('edited_by')->nullable()->constrained('users')->nullOnDelete();
            $table->string('previous_excerpt');
            $table->string('new_excerpt');
            $table->timestamps();
        });

        Schema::create('comment_blocklists', function (Blueprint $table): void {
            $table->id();
            $table->string('type');
            $table->string('hash')->index();
            $table->string('label')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['type', 'hash']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('comment_blocklists');
        Schema::dropIfExists('comment_edit_histories');

        Schema::table('blog_posts', function (Blueprint $table): void {
            $table->dropColumn(['comments_enabled', 'comments_closed_at', 'comments_moderation_required']);
        });

        Schema::table('comments', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('edited_by');
            $table->dropColumn(['edited_at', 'manual_override', 'original_provider_decision', 'reply_depth']);
        });
    }
};
