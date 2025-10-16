<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('reviews', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->decimal('rating', 2, 1);
            $table->text('comment')->nullable();
            $table->json('pros')->nullable();
            $table->json('cons')->nullable();
            $table->json('images')->nullable();
            $table->boolean('is_verified_purchase')->default(false);
            $table->integer('helpful_votes')->default(0);
            $table->timestamps();
            $table->softDeletes();
        });
    }

    public function down()
    {
        Schema::dropIfExists('reviews');
    }
};