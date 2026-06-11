<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('user_audit_events', function (Blueprint $table): void {
            $table->string('module')->nullable()->after('event')->index();
            $table->string('action')->nullable()->after('module')->index();
            $table->string('severity')->default('info')->after('action')->index();
            $table->uuid('correlation_id')->nullable()->after('severity')->index();
            $table->string('ip_address', 45)->nullable()->after('correlation_id');
            $table->text('user_agent')->nullable()->after('ip_address');
            $table->string('target_type')->nullable()->after('user_agent');
            $table->unsignedBigInteger('target_id')->nullable()->after('target_type');
            $table->string('target_url')->nullable()->after('target_id');
            $table->string('route_name')->nullable()->after('target_url');
            $table->string('request_method', 12)->nullable()->after('route_name');
        });
    }

    public function down(): void
    {
        Schema::table('user_audit_events', function (Blueprint $table): void {
            $table->dropColumn([
                'module',
                'action',
                'severity',
                'correlation_id',
                'ip_address',
                'user_agent',
                'target_type',
                'target_id',
                'target_url',
                'route_name',
                'request_method',
            ]);
        });
    }
};
