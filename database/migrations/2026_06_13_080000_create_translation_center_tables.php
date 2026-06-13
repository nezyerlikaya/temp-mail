<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('translation_sources', function (Blueprint $table): void {
            $table->id();
            $table->string('group_key', 80)->index();
            $table->string('translation_key')->unique();
            $table->text('source_value');
            $table->text('description')->nullable();
            $table->string('value_type', 32)->default('short_text');
            $table->boolean('is_required')->default(true)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(100);
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['group_key', 'sort_order']);
        });

        Schema::create('translation_values', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('translation_source_id')->constrained('translation_sources')->cascadeOnDelete();
            $table->foreignId('locale_id')->constrained('locales')->cascadeOnDelete();
            $table->text('value')->nullable();
            $table->string('status', 24)->default('missing')->index();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['translation_source_id', 'locale_id']);
            $table->index(['locale_id', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('translation_values');
        Schema::dropIfExists('translation_sources');
    }
};
