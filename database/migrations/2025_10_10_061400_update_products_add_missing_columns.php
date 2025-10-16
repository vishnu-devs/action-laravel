<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (!Schema::hasColumn('products', 'brand')) {
                $table->string('brand')->nullable()->after('category');
            }
            if (!Schema::hasColumn('products', 'model')) {
                $table->string('model')->nullable()->after('brand');
            }
            if (!Schema::hasColumn('products', 'mrp')) {
                $table->decimal('mrp', 10, 2)->nullable()->after('price');
            }
            if (!Schema::hasColumn('products', 'discount_percentage')) {
                $table->decimal('discount_percentage', 5, 2)->default(0);
            }
            if (!Schema::hasColumn('products', 'specifications')) {
                $table->json('specifications')->nullable();
            }
            if (!Schema::hasColumn('products', 'highlights')) {
                $table->json('highlights')->nullable();
            }
            if (!Schema::hasColumn('products', 'main_image')) {
                $table->string('main_image')->nullable();
            }
            if (!Schema::hasColumn('products', 'additional_images')) {
                $table->json('additional_images')->nullable();
            }
            if (!Schema::hasColumn('products', 'is_featured')) {
                $table->boolean('is_featured')->default(false);
            }
            if (!Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true);
            }
            if (!Schema::hasColumn('products', 'weight')) {
                $table->decimal('weight', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'length')) {
                $table->decimal('length', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'width')) {
                $table->decimal('width', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'height')) {
                $table->decimal('height', 8, 2)->nullable();
            }
            if (!Schema::hasColumn('products', 'free_shipping')) {
                $table->boolean('free_shipping')->default(false);
            }
            if (!Schema::hasColumn('products', 'average_rating')) {
                $table->decimal('average_rating', 2, 1)->default(0);
            }
            if (!Schema::hasColumn('products', 'review_count')) {
                $table->integer('review_count')->default(0);
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $columns = [
                'brand', 'model', 'mrp', 'discount_percentage', 'specifications', 'highlights',
                'main_image', 'additional_images', 'is_featured', 'is_active', 'weight',
                'length', 'width', 'height', 'free_shipping', 'average_rating', 'review_count'
            ];
            foreach ($columns as $col) {
                if (Schema::hasColumn('products', $col)) {
                    try { $table->dropColumn($col); } catch (\Throwable $e) { /* ignore */ }
                }
            }
        });
    }
};