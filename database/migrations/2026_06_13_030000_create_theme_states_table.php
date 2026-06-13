<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('theme_states', function (Blueprint $table): void {
            $table->id();
            $table->string('slug')->unique();
            $table->string('status')->default('inactive');
            $table->timestamp('last_activated_at')->nullable();
            $table->timestamp('last_deactivated_at')->nullable();
            $table->foreignId('activated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['status', 'last_activated_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('theme_states');
    }
};
