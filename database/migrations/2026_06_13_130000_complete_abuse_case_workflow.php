<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('abuse_reports', function (Blueprint $table): void {
            $table->foreignId('related_comment_id')->nullable()->after('reported_user_id')->constrained('comments')->nullOnDelete();
            $table->string('resolution_outcome', 64)->nullable()->after('internal_notes');
            $table->text('resolution_reason')->nullable()->after('resolution_outcome');
            $table->text('resolution_summary')->nullable()->after('resolution_reason');
            $table->foreignId('resolved_by')->nullable()->after('resolution_summary')->constrained('users')->nullOnDelete();
            $table->timestamp('resolved_at')->nullable()->after('resolved_by');
            $table->timestamp('reopened_at')->nullable()->after('resolved_at');
            $table->timestamp('archived_at')->nullable()->after('reopened_at');
            $table->timestamp('retention_review_at')->nullable()->after('archived_at');
            $table->string('reporter_response_subject')->nullable()->after('reporter_notification_status');
            $table->text('reporter_response_body')->nullable()->after('reporter_response_subject');
            $table->timestamp('reporter_response_prepared_at')->nullable()->after('reporter_response_body');

            $table->index(['status', 'retention_review_at']);
        });

        Schema::create('abuse_case_notes', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('abuse_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('author_id')->nullable()->constrained('users')->nullOnDelete();
            $table->text('body');
            $table->timestamps();
            $table->index(['abuse_report_id', 'created_at']);
        });

        Schema::create('abuse_evidences', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('abuse_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_asset_id')->constrained()->restrictOnDelete();
            $table->string('label')->nullable();
            $table->boolean('is_sensitive')->default(true);
            $table->string('private_disk', 32)->default('local');
            $table->string('private_path');
            $table->foreignId('added_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['abuse_report_id', 'media_asset_id']);
        });

        Schema::create('abuse_case_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('abuse_report_id')->constrained()->cascadeOnDelete();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event_type', 64);
            $table->string('summary');
            $table->uuid('correlation_id')->nullable()->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
            $table->index(['abuse_report_id', 'created_at']);
        });

        Schema::create('abuse_blocklist_entries', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('abuse_report_id')->nullable()->constrained()->nullOnDelete();
            $table->string('type', 40);
            $table->string('value_hash', 64);
            $table->text('encrypted_value');
            $table->string('value_preview', 120);
            $table->string('status', 24)->default('active');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();
            $table->unique(['type', 'value_hash']);
            $table->index(['status', 'type']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('abuse_blocklist_entries');
        Schema::dropIfExists('abuse_case_events');
        Schema::dropIfExists('abuse_evidences');
        Schema::dropIfExists('abuse_case_notes');

        Schema::table('abuse_reports', function (Blueprint $table): void {
            $table->dropForeign(['related_comment_id']);
            $table->dropForeign(['resolved_by']);
            $table->dropIndex(['status', 'retention_review_at']);
            $table->dropColumn([
                'related_comment_id', 'resolution_outcome', 'resolution_reason', 'resolution_summary',
                'resolved_by', 'resolved_at', 'reopened_at', 'archived_at', 'retention_review_at',
                'reporter_response_subject', 'reporter_response_body', 'reporter_response_prepared_at',
            ]);
        });
    }
};
