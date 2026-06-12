<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('seo_records', function (Blueprint $table): void {
            $table->string('twitter_title', 180)->nullable()->after('twitter_card');
            $table->text('twitter_description')->nullable()->after('twitter_title');
            $table->foreignId('twitter_image_media_id')->nullable()->after('twitter_description')->constrained('media_assets')->nullOnDelete();
            $table->string('breadcrumb_title', 120)->nullable()->after('schema_json');
        });
    }

    public function down(): void
    {
        Schema::table('seo_records', function (Blueprint $table): void {
            $table->dropConstrainedForeignId('twitter_image_media_id');
            $table->dropColumn(['twitter_title', 'twitter_description', 'breadcrumb_title']);
        });
    }
};
