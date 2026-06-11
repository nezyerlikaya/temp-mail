<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_usages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('media_asset_id')->constrained('media_assets')->cascadeOnDelete();
            $table->string('module', 64);
            $table->string('usage_context', 64);
            $table->string('slot', 96);
            $table->string('usable_type')->nullable();
            $table->string('usable_id', 64)->nullable();
            $table->string('label')->nullable();
            $table->json('metadata')->nullable();
            $table->foreignId('attached_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['media_asset_id', 'module', 'usage_context', 'slot', 'usable_type', 'usable_id'], 'media_usages_unique_usage');
            $table->index(['module', 'usage_context']);
            $table->index(['usable_type', 'usable_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_usages');
    }
};
