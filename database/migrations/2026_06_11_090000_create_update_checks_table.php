<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('update_checks', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('channel', 24);
            $table->string('current_version', 64);
            $table->string('latest_version', 64)->nullable();
            $table->string('status', 32);
            $table->string('endpoint', 512);
            $table->boolean('https_endpoint')->default(false);
            $table->boolean('signed_manifest')->default(false);
            $table->string('checksum', 128)->nullable();
            $table->text('signature')->nullable();
            $table->json('manifest')->nullable();
            $table->json('compatibility')->nullable();
            $table->text('error_message')->nullable();
            $table->foreignId('checked_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('checked_at')->nullable();
            $table->timestamps();

            $table->index(['channel', 'status']);
            $table->index('checked_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('update_checks');
    }
};
