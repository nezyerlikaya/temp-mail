<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sections', function (Blueprint $table): void {
            $table->string('device_visibility', 32)->default('all')->after('visibility');
            $table->index('device_visibility');
        });
    }

    public function down(): void
    {
        Schema::table('sections', function (Blueprint $table): void {
            $table->dropIndex(['device_visibility']);
            $table->dropColumn('device_visibility');
        });
    }
};
