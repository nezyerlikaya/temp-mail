<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('seo_templates', function (Blueprint $table): void {
            $table->id();
            $table->string('target_type', 64);
            $table->foreignId('locale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name', 120);
            $table->string('meta_title_template', 180)->nullable();
            $table->string('meta_description_template', 320)->nullable();
            $table->string('og_title_template', 180)->nullable();
            $table->string('og_description_template', 320)->nullable();
            $table->string('schema_type', 80)->nullable();
            $table->json('schema_json_template')->nullable();
            $table->boolean('is_active')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['target_type', 'locale_id']);
        });

        Schema::create('seo_redirects', function (Blueprint $table): void {
            $table->id();
            $table->string('source_path', 255)->unique();
            $table->string('target_url', 500);
            $table->unsignedSmallInteger('status_code')->default(301);
            $table->boolean('is_active')->default(true);
            $table->text('notes')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('seo_versions', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('seo_record_id')->constrained('seo_records')->cascadeOnDelete();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->json('snapshot');
            $table->string('reason', 160)->default('manual_update');
            $table->timestamps();

            $table->index(['seo_record_id', 'created_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('seo_versions');
        Schema::dropIfExists('seo_redirects');
        Schema::dropIfExists('seo_templates');
    }
};
