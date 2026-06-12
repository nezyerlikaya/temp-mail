<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('mailbox_messages', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('mailbox_id')->constrained()->cascadeOnDelete();
            $table->string('sender_email');
            $table->string('sender_name')->nullable();
            $table->string('subject')->nullable();
            $table->text('preview_text')->nullable();
            $table->longText('plain_text_body')->nullable();
            $table->longText('sanitized_html_body')->nullable();
            $table->json('raw_headers')->nullable();
            $table->unsignedInteger('attachment_count')->default(0);
            $table->unsignedBigInteger('message_size')->default(0);
            $table->timestamp('received_at');
            $table->timestamp('read_at')->nullable();
            $table->timestamp('deleted_at')->nullable();
            $table->timestamp('quarantined_at')->nullable();
            $table->timestamps();

            $table->index(['mailbox_id', 'received_at']);
            $table->index(['mailbox_id', 'read_at']);
            $table->index(['mailbox_id', 'deleted_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('mailbox_messages');
    }
};
