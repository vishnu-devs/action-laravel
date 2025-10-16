<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vendor_details', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained()->cascadeOnDelete();
            $table->string('business_type')->nullable(); // Proprietorship, Partnership, LLC, etc.
            $table->string('gst_number')->nullable();
            $table->string('pan_number')->nullable();
            $table->text('business_address');
            $table->string('city');
            $table->string('state');
            $table->string('pincode');
            $table->string('contact_person_name');
            $table->string('contact_person_phone');
            $table->string('alternate_phone')->nullable();
            
            // Bank Details
            $table->string('bank_name');
            $table->string('account_number');
            $table->string('ifsc_code');
            $table->string('branch_name');
            
            // Document Paths
            $table->string('gst_certificate_path')->nullable();
            $table->string('pan_card_path')->nullable();
            $table->string('business_license_path')->nullable();
            $table->string('bank_statement_path')->nullable();
            
            // Approval Status
            $table->enum('status', [0, 1, 2])->default(0);
            $table->text('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vendor_details');
    }
};