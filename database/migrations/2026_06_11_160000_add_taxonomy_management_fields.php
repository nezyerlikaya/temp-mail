<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('blog_categories', function (Blueprint $table): void {
            $table->string('status', 32)->default('active')->after('description');
            $table->unsignedInteger('sort_order')->default(0)->after('status');
            $table->index(['locale_id', 'status']);
            $table->index('sort_order');
        });

        Schema::table('blog_tags', function (Blueprint $table): void {
            $table->text('description')->nullable()->after('slug');
            $table->string('status', 32)->default('active')->after('description');
            $table->index(['locale_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::table('blog_tags', function (Blueprint $table): void {
            $table->dropIndex(['locale_id', 'status']);
            $table->dropColumn(['description', 'status']);
        });

        Schema::table('blog_categories', function (Blueprint $table): void {
            $table->dropIndex(['locale_id', 'status']);
            $table->dropIndex(['sort_order']);
            $table->dropColumn(['status', 'sort_order']);
        });
    }
};
