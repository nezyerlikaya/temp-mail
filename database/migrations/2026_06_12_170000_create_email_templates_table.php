<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('email_templates', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('locale_id')->constrained()->cascadeOnDelete();
            $table->string('template_key', 80);
            $table->string('subject', 180);
            $table->string('preheader', 240)->nullable();
            $table->longText('html_body');
            $table->longText('plain_text_body')->nullable();
            $table->string('status', 20)->default('draft');
            $table->foreignId('updated_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamps();

            $table->unique(['locale_id', 'template_key']);
            $table->index(['template_key', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('email_templates');
    }
};
