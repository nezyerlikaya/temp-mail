<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailbox_rules', function (Blueprint $table): void {
            $table->id();
            $table->unsignedInteger('guest_lifetime_minutes')->default(1440);
            $table->unsignedInteger('registered_lifetime_minutes')->default(10080);
            $table->unsignedInteger('premium_lifetime_minutes')->default(43200);
            $table->unsignedSmallInteger('maximum_active_mailboxes')->default(10);
            $table->unsignedSmallInteger('maximum_messages_per_inbox')->default(100);
            $table->unsignedInteger('maximum_message_size_kb')->default(10240);
            $table->string('attachment_policy', 24)->default('disabled');
            $table->boolean('auto_delete_expired')->default(false);
            $table->unsignedSmallInteger('expired_cleanup_delay_hours')->default(24);
            $table->unsignedSmallInteger('inbox_refresh_rate_limit')->default(30);
            $table->unsignedTinyInteger('random_alias_length')->default(12);
            $table->string('random_alias_format', 24)->default('alphanumeric');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
        });

        Schema::create('mailbox_delivery_health_checks', function (Blueprint $table): void {
            $table->id();
            $table->string('status', 24);
            $table->json('summary');
            $table->timestamp('checked_at');
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->index(['status', 'checked_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailbox_delivery_health_checks');
        Schema::dropIfExists('mailbox_rules');
    }
};
