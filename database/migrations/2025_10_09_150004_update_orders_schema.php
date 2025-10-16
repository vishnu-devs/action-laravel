<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'product_id')) {
                // Drop foreign key and column if present
                try { $table->dropConstrainedForeignId('product_id'); } catch (\Throwable $e) {}
                if (Schema::hasColumn('orders', 'product_id')) {
                    $table->dropColumn('product_id');
                }
            }

            if (Schema::hasColumn('orders', 'company_id')) {
                try { $table->dropConstrainedForeignId('company_id'); } catch (\Throwable $e) {}
                if (Schema::hasColumn('orders', 'company_id')) {
                    $table->dropColumn('company_id');
                }
            }

            if (Schema::hasColumn('orders', 'quantity')) {
                $table->dropColumn('quantity');
            }

            if (!Schema::hasColumn('orders', 'shipping_address')) {
                $table->text('shipping_address')->nullable();
            }
            if (!Schema::hasColumn('orders', 'payment_method')) {
                $table->string('payment_method')->default('cod');
            }
            if (!Schema::hasColumn('orders', 'total_amount')) {
                $table->decimal('total_amount', 10, 2)->default(0.00);
            }
            if (!Schema::hasColumn('orders', 'status')) {
                $table->string('status')->default('processing');
            }
            if (!Schema::hasColumn('orders', 'contact_name')) {
                $table->string('contact_name')->nullable();
            }
            if (!Schema::hasColumn('orders', 'contact_email')) {
                $table->string('contact_email')->nullable();
            }
            if (!Schema::hasColumn('orders', 'contact_phone')) {
                $table->string('contact_phone')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            if (Schema::hasColumn('orders', 'shipping_address')) {
                $table->dropColumn('shipping_address');
            }
            if (Schema::hasColumn('orders', 'payment_method')) {
                $table->dropColumn('payment_method');
            }
            if (Schema::hasColumn('orders', 'total_amount')) {
                $table->dropColumn('total_amount');
            }
            if (Schema::hasColumn('orders', 'status')) {
                $table->dropColumn('status');
            }
            if (Schema::hasColumn('orders', 'contact_name')) {
                $table->dropColumn('contact_name');
            }
            if (Schema::hasColumn('orders', 'contact_email')) {
                $table->dropColumn('contact_email');
            }
            if (Schema::hasColumn('orders', 'contact_phone')) {
                $table->dropColumn('contact_phone');
            }

            if (!Schema::hasColumn('orders', 'product_id')) {
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('orders', 'company_id')) {
                $table->foreignId('company_id')->constrained()->cascadeOnDelete();
            }
            if (!Schema::hasColumn('orders', 'quantity')) {
                $table->integer('quantity')->default(1);
            }
        });
    }
};