<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('locale_id')->constrained('locales')->cascadeOnDelete();
            $table->string('section_type', 48);
            $table->string('placement', 80);
            $table->string('title');
            $table->text('subtitle')->nullable();
            $table->longText('content')->nullable();
            $table->json('settings')->nullable();
            $table->string('status', 32)->default('draft');
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('visibility', 32)->default('public');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('trashed_at')->nullable();
            $table->timestamps();

            $table->index(['locale_id', 'section_type']);
            $table->index(['placement', 'status']);
            $table->index(['visibility', 'status']);
            $table->index('sort_order');
        });

        Schema::create('section_items', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('section_id')->constrained('sections')->cascadeOnDelete();
            $table->string('title');
            $table->longText('content')->nullable();
            $table->string('status', 32)->default('active');
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->index(['section_id', 'status']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('section_items');
        Schema::dropIfExists('sections');
    }
};
