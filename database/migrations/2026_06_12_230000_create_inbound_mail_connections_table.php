<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('inbound_mail_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('domain_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(993);
            $table->string('encryption', 12)->default('ssl');
            $table->string('username');
            $table->text('encrypted_password');
            $table->string('mailbox')->default('INBOX');
            $table->unsignedSmallInteger('connection_timeout')->default(15);
            $table->boolean('validate_certificate')->default(true);
            $table->boolean('is_active')->default(false);
            $table->string('status', 24)->default('not_tested');
            $table->json('last_test_result')->nullable();
            $table->json('health_history')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['domain_id', 'name']);
            $table->index(['is_active', 'status']);
            $table->index('last_tested_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('inbound_mail_connections');
    }
};
