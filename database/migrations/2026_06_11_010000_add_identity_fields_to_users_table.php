<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('display_name')->nullable()->after('name');
            $table->string('username')->nullable()->unique()->after('display_name');
            $table->string('status')->default('active')->index()->after('email_verified_at');
            $table->string('role')->default('member')->index()->after('status');
            $table->string('timezone')->default('UTC')->after('role');
            $table->string('language_preference', 12)->default('en')->after('timezone');
            $table->text('bio')->nullable()->after('language_preference');
            $table->string('website')->nullable()->after('bio');
            $table->unsignedBigInteger('avatar_media_id')->nullable()->index()->after('website');
        });

        DB::table('users')->where('is_admin', true)->update(['role' => 'admin']);
    }

    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropUnique(['username']);
            $table->dropIndex(['status']);
            $table->dropIndex(['role']);
            $table->dropIndex(['avatar_media_id']);
            $table->dropColumn([
                'display_name',
                'username',
                'status',
                'role',
                'timezone',
                'language_preference',
                'bio',
                'website',
                'avatar_media_id',
            ]);
        });
    }
};
