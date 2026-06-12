<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('domains', function (Blueprint $table): void {
            $table->id();
            $table->string('domain_name')->unique();
            $table->string('display_name');
            $table->boolean('is_active')->default(false);
            $table->boolean('is_public')->default(false);
            $table->boolean('catch_all_ready')->default(false);
            $table->boolean('is_default')->default(false);
            $table->unsignedInteger('sort_order')->default(100);
            $table->string('status', 24)->default('draft');
            $table->json('dns_checks')->nullable();
            $table->timestamp('last_checked_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'is_default']);
            $table->index(['status', 'is_public']);
            $table->index('sort_order');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('domains');
    }
};
