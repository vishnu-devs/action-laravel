<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('vendor_requests', function (Blueprint $table) {
            if (!Schema::hasColumn('vendor_requests', 'contact_person_name')) {
                $table->string('contact_person_name');
            }
            if (!Schema::hasColumn('vendor_requests', 'contact_person_phone')) {
                $table->string('contact_person_phone');
            }
            if (!Schema::hasColumn('vendor_requests', 'alternate_phone')) {
                $table->string('alternate_phone')->nullable();
            }
            if (!Schema::hasColumn('vendor_requests', 'branch_name')) {
                $table->string('branch_name');
            }
            if (!Schema::hasColumn('vendor_requests', 'rejection_reason')) {
                $table->text('rejection_reason')->nullable();
            }
            if (!Schema::hasColumn('vendor_requests', 'approved_at')) {
                $table->timestamp('approved_at')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('vendor_requests', function (Blueprint $table) {
            if (Schema::hasColumn('vendor_requests', 'contact_person_name')) {
                $table->dropColumn('contact_person_name');
            }
            if (Schema::hasColumn('vendor_requests', 'contact_person_phone')) {
                $table->dropColumn('contact_person_phone');
            }
            if (Schema::hasColumn('vendor_requests', 'alternate_phone')) {
                $table->dropColumn('alternate_phone');
            }
            if (Schema::hasColumn('vendor_requests', 'branch_name')) {
                $table->dropColumn('branch_name');
            }
            if (Schema::hasColumn('vendor_requests', 'rejection_reason')) {
                $table->dropColumn('rejection_reason');
            }
            if (Schema::hasColumn('vendor_requests', 'approved_at')) {
                $table->dropColumn('approved_at');
            }
        });
    }
};