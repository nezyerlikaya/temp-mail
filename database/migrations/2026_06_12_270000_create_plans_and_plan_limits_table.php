<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('plans', function (Blueprint $table): void {
            $table->id();
            $table->string('key')->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true);
            $table->decimal('monthly_price', 10, 2)->default(0);
            $table->decimal('yearly_price', 10, 2)->default(0);
            $table->string('currency', 3)->default('USD');
            $table->unsignedInteger('sort_order')->default(100);
            $table->string('billing_provider', 24)->default('manual');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('billing_provider');
        });

        Schema::create('plan_limits', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('plan_id')->unique()->constrained()->cascadeOnDelete();
            $table->unsignedSmallInteger('maximum_active_inboxes');
            $table->unsignedInteger('inbox_lifetime_minutes');
            $table->unsignedInteger('maximum_messages_per_inbox');
            $table->unsignedInteger('maximum_message_size_kb');
            $table->boolean('custom_alias_allowed')->default(false);
            $table->boolean('custom_domain_allowed')->default(false);
            $table->boolean('api_access_allowed')->default(false);
            $table->unsignedInteger('api_request_limit')->default(0);
            $table->boolean('ads_enabled')->default(true);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('plan_limits');
        Schema::dropIfExists('plans');
    }
};
