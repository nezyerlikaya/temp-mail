<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('notification_rules', function (Blueprint $table): void {
            $table->id();
            $table->string('event_key', 100)->unique();
            $table->string('severity', 20)->default('info');
            $table->boolean('in_app_enabled')->default(true);
            $table->boolean('email_enabled')->default(false);
            $table->json('recipient_roles')->nullable();
            $table->string('digest_mode', 30)->default('immediate');
            $table->boolean('quiet_hours_enabled')->default(false);
            $table->time('quiet_hours_start')->nullable();
            $table->time('quiet_hours_end')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();

            $table->index(['severity', 'is_active']);
        });

        Schema::table('system_notifications', function (Blueprint $table): void {
            $table->unsignedInteger('occurrence_count')->default(1)->after('archived_at');
            $table->timestamp('first_occurred_at')->nullable()->after('occurrence_count');
            $table->timestamp('last_occurred_at')->nullable()->after('first_occurred_at');
            $table->timestamp('snoozed_until')->nullable()->after('last_occurred_at');
            $table->string('deduplication_key')->nullable()->after('snoozed_until');
            $table->timestamp('digest_pending_at')->nullable()->after('deduplication_key');
            $table->timestamp('digest_sent_at')->nullable()->after('digest_pending_at');
            $table->string('digest_status', 40)->nullable()->after('digest_sent_at');

            $table->index(['recipient_user_id', 'deduplication_key']);
            $table->index(['snoozed_until', 'archived_at']);
            $table->index(['digest_status', 'digest_pending_at']);
        });
    }

    public function down(): void
    {
        Schema::table('system_notifications', function (Blueprint $table): void {
            $table->dropIndex(['recipient_user_id', 'deduplication_key']);
            $table->dropIndex(['snoozed_until', 'archived_at']);
            $table->dropIndex(['digest_status', 'digest_pending_at']);
            $table->dropColumn([
                'occurrence_count',
                'first_occurred_at',
                'last_occurred_at',
                'snoozed_until',
                'deduplication_key',
                'digest_pending_at',
                'digest_sent_at',
                'digest_status',
            ]);
        });

        Schema::dropIfExists('notification_rules');
    }
};
