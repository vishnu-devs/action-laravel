<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('vendor_requests', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->onDelete('cascade');

            $table->string('business_name');
            $table->string('business_type');
            $table->string('gst_number')->unique();
            $table->string('pan_number')->unique();
            $table->text('address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode');
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('ifsc_code');

            // Additional columns from update migration
            $table->string('contact_person_name');
            $table->string('contact_person_phone');
            $table->string('alternate_phone')->nullable();
            $table->string('branch_name');
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();

            $table->tinyInteger('status')->default(0); // 0: pending, 1: approved, 2: rejected
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('vendor_requests');
    }
};
