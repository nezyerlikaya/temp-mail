<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('audit_retention_settings', function (Blueprint $table): void {
            $table->id();
            $table->unsignedSmallInteger('retention_days')->default(180);
            $table->boolean('preserve_critical')->default(true);
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('audit_retention_settings');
    }
};
