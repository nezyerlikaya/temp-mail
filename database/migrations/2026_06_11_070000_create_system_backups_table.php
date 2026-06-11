<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('system_backups', function (Blueprint $table): void {
            $table->id();
            $table->uuid('uuid')->unique();
            $table->string('type')->index();
            $table->string('status')->default('completed')->index();
            $table->string('disk')->default('local');
            $table->string('path')->nullable();
            $table->string('filename')->nullable();
            $table->unsignedBigInteger('size_bytes')->default(0);
            $table->string('checksum')->nullable();
            $table->json('manifest')->nullable();
            $table->text('failure_reason')->nullable();
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('completed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('system_backups');
    }
};
