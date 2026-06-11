<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('media_assets')) {
            DB::table('media_assets')
                ->where('status', 'draft')
                ->update(['status' => 'hidden']);
        }
    }

    public function down(): void
    {
        if (Schema::hasTable('media_assets')) {
            DB::table('media_assets')
                ->where('status', 'hidden')
                ->update(['status' => 'draft']);
        }
    }
};
