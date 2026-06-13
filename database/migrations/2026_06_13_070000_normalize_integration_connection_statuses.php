<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('integration_settings')) {
            return;
        }

        DB::table('integration_settings')
            ->where('connection_status', 'ready')
            ->update(['connection_status' => 'connected']);

        DB::table('integration_settings')
            ->whereIn('connection_status', ['missing_configuration', 'not_configured'])
            ->update(['connection_status' => 'not_tested']);

        DB::table('integration_settings')
            ->where('is_active', false)
            ->whereIn('connection_status', ['not_tested', 'connected', 'degraded', 'failed'])
            ->update(['connection_status' => 'disabled']);
    }

    public function down(): void
    {
        if (! Schema::hasTable('integration_settings')) {
            return;
        }

        DB::table('integration_settings')
            ->where('connection_status', 'disabled')
            ->update(['connection_status' => 'not_configured']);
    }
};
