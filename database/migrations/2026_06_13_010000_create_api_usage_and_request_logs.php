<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('mailboxes', function (Blueprint $table): void {
            $table->foreignId('api_key_id')->nullable()->after('created_by')->constrained('api_keys')->nullOnDelete();
            $table->string('api_environment', 12)->nullable()->after('api_key_id');
            $table->index(['user_id', 'api_environment', 'status']);
        });

        Schema::create('api_usage_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('api_key_id')->constrained('api_keys')->cascadeOnDelete();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->date('usage_date');
            $table->unsignedInteger('request_count')->default(0);
            $table->timestamps();

            $table->unique(['api_key_id', 'usage_date']);
            $table->index(['user_id', 'usage_date']);
        });

        Schema::create('api_request_logs', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('api_key_id')->nullable()->constrained('api_keys')->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('key_prefix', 32)->nullable();
            $table->string('endpoint');
            $table->string('method', 12);
            $table->unsignedSmallInteger('response_status');
            $table->unsignedInteger('duration_ms')->default(0);
            $table->timestamp('requested_at');
            $table->timestamps();

            $table->index(['api_key_id', 'requested_at']);
            $table->index(['user_id', 'requested_at']);
            $table->index(['response_status', 'requested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('api_request_logs');
        Schema::dropIfExists('api_usage_events');
        Schema::table('mailboxes', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('api_key_id');
            $table->dropColumn('api_environment');
        });
    }
};
