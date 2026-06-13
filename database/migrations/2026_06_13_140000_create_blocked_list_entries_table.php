<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('blocked_list_entries', function (Blueprint $table): void {
            $table->id();
            $table->string('entry_type', 48);
            $table->string('normalized_hash', 64);
            $table->text('encrypted_normalized_value')->nullable();
            $table->string('display_value');
            $table->text('reason');
            $table->string('source', 40)->default('manual');
            $table->string('status', 24)->default('active');
            $table->timestamp('starts_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('related_abuse_report_id')->nullable()->constrained('abuse_reports')->nullOnDelete();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->index(['entry_type', 'status']);
            $table->index(['source', 'status']);
            $table->index(['created_by', 'created_at']);
            $table->index(['expires_at', 'status']);
            $table->unique(['entry_type', 'normalized_hash', 'status'], 'blocked_list_entries_unique_status_rule');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('blocked_list_entries');
    }
};
