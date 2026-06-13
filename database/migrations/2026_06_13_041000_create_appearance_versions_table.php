<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('appearance_settings', function (Blueprint $table): void {
            $table->foreignId('published_by')->nullable()->after('published_at')->constrained('users')->nullOnDelete();
        });

        Schema::create('appearance_versions', function (Blueprint $table): void {
            $table->id();
            $table->string('theme_slug');
            $table->unsignedInteger('version_number');
            $table->json('tokens');
            $table->json('contrast_report');
            $table->foreignId('published_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('source_version_id')->nullable()->constrained('appearance_versions')->nullOnDelete();
            $table->timestamps();

            $table->unique(['theme_slug', 'version_number']);
            $table->index(['theme_slug', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('appearance_versions');

        Schema::table('appearance_settings', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('published_by');
        });
    }
};
