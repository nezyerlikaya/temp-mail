<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! DB::table('users')->where('role', 'owner')->exists()) {
            $firstAdminId = DB::table('users')->where('role', 'admin')->oldest('id')->value('id');

            if ($firstAdminId !== null) {
                DB::table('users')->where('id', $firstAdminId)->update([
                    'role' => 'owner',
                    'is_admin' => true,
                ]);
            }
        }

        Schema::create('user_audit_events', function (Blueprint $table): void {
            $table->id();
            $table->foreignId('actor_id')->nullable()->constrained('users')->nullOnDelete();
            $table->foreignId('subject_id')->nullable()->constrained('users')->nullOnDelete();
            $table->string('event')->index();
            $table->json('metadata')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('user_audit_events');
    }
};
