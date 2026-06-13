<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('abuse_reports', function (Blueprint $table): void {
            $table->id();
            $table->string('case_reference')->unique();
            $table->string('report_type')->index();
            $table->string('priority')->default('normal')->index();
            $table->string('status')->default('new')->index();
            $table->string('reporter_name');
            $table->string('reporter_email')->index();
            $table->string('reporter_email_hash')->index();
            $table->string('subject');
            $table->text('description');
            $table->string('description_excerpt');
            $table->foreignId('reported_mailbox_id')->nullable()->constrained('mailboxes')->nullOnDelete();
            $table->foreignId('reported_domain_id')->nullable()->constrained('domains')->nullOnDelete();
            $table->foreignId('reported_user_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('related_url')->nullable();
            $table->json('evidence_media_ids')->nullable();
            $table->foreignId('assigned_to')->nullable()->constrained('users')->nullOnDelete();
            $table->text('internal_notes')->nullable();
            $table->string('submitted_ip_hash')->nullable()->index();
            $table->json('bot_protection_readiness')->nullable();
            $table->string('reporter_notification_status')->default('ready');
            $table->timestamps();
            $table->index(['status', 'priority']);
            $table->index(['assigned_to', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abuse_reports');
    }
};
