<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->string('public_author_slug')->nullable()->unique()->after('avatar_media_id');
            $table->text('author_bio')->nullable()->after('public_author_slug');
            $table->json('social_links')->nullable()->after('author_bio');
            $table->boolean('author_profile_active')->default(false)->index()->after('social_links');
            $table->boolean('featured_author')->default(false)->index()->after('author_profile_active');
            $table->string('avatar_color', 7)->default('#0f766e')->after('featured_author');
            $table->string('current_plan_reference')->nullable()->index()->after('avatar_color');
            $table->string('membership_status')->default('none')->index()->after('current_plan_reference');
            $table->timestamp('premium_starts_at')->nullable()->after('membership_status');
            $table->timestamp('premium_ends_at')->nullable()->after('premium_starts_at');
            $table->foreignId('membership_granted_by')->nullable()->after('premium_ends_at')->constrained('users')->nullOnDelete();
            $table->softDeletes();
        });
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table): void {
            $table->dropForeign(['membership_granted_by']);
            $table->dropUnique(['public_author_slug']);
            $table->dropIndex(['author_profile_active']);
            $table->dropIndex(['featured_author']);
            $table->dropIndex(['current_plan_reference']);
            $table->dropIndex(['membership_status']);
            $table->dropColumn([
                'public_author_slug',
                'author_bio',
                'social_links',
                'author_profile_active',
                'featured_author',
                'avatar_color',
                'current_plan_reference',
                'membership_status',
                'premium_starts_at',
                'premium_ends_at',
                'membership_granted_by',
                'deleted_at',
            ]);
        });
    }
};
