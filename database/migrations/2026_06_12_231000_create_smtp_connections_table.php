<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('smtp_connections', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('domain_id')->nullable()->constrained()->nullOnDelete();
            $table->string('name');
            $table->string('host');
            $table->unsignedSmallInteger('port')->default(587);
            $table->string('encryption', 12)->default('tls');
            $table->string('username');
            $table->text('encrypted_password');
            $table->string('from_email');
            $table->string('from_name');
            $table->string('reply_to_email')->nullable();
            $table->boolean('reply_to_ready')->default(false);
            $table->unsignedSmallInteger('connection_timeout')->default(15);
            $table->boolean('validate_certificate')->default(true);
            $table->boolean('is_active')->default(false);
            $table->boolean('is_default')->default(false);
            $table->string('status', 24)->default('not_tested');
            $table->json('last_test_result')->nullable();
            $table->json('health_history')->nullable();
            $table->timestamp('last_tested_at')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique('name');
            $table->index(['is_active', 'is_default']);
            $table->index(['status', 'last_tested_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('smtp_connections');
    }
};
