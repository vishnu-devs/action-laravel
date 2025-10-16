<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('personal_access_tokens', function (Blueprint $table) {
            if (!Schema::hasColumn('personal_access_tokens', 'device_fingerprint')) {
                $table->mediumText('device_fingerprint')->nullable()->after('ip_address');
            }
        });

        // Ensure column type is MEDIUMTEXT in MySQL
        try {
            DB::statement('ALTER TABLE personal_access_tokens MODIFY device_fingerprint MEDIUMTEXT NULL');
        } catch (\Throwable $e) {
            // ignore if not supported
        }
    }

    public function down(): void
    {
        // Revert to VARCHAR(255) if needed
        try {
            DB::statement('ALTER TABLE personal_access_tokens MODIFY device_fingerprint VARCHAR(255) NULL');
        } catch (\Throwable $e) {
            // ignore if not supported
        }
    }
};