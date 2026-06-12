<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailboxes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('domain_id')->constrained()->restrictOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('locale_id')->nullable()->constrained()->nullOnDelete();
            $table->string('address')->unique();
            $table->string('local_part');
            $table->string('mailbox_type', 24)->default('guest');
            $table->string('status', 24)->default('active');
            $table->timestamp('expires_at')->nullable();
            $table->timestamp('last_activity_at')->nullable();
            $table->unsignedInteger('message_count')->default(0);
            $table->string('created_ip_hash', 64)->nullable();
            $table->json('activity_timeline')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['domain_id', 'local_part']);
            $table->index(['status', 'mailbox_type']);
            $table->index(['domain_id', 'status']);
            $table->index(['user_id', 'created_at']);
            $table->index('expires_at');
            $table->index('last_activity_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailboxes');
    }
};
