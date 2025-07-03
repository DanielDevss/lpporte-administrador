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
        Schema::create('products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('brand_id')->constrained()->onDelete('cascade')->onUpdate('cascade');
            $table->string('title', 120);
            $table->string('slug', 155)->unique();
            $table->enum('status', ['activo', 'pausado'])->default('activo');
            $table->string('description_short', 170)->nullable();
            $table->text('description')->nullable();
            $table->string('thumb', 255);
            $table->string('price', 25);
            $table->string('price_wholesale', 25);
            $table->string('price_basic_plan', 25);
            $table->string('price_premium_plan', 25);
            $table->string('stripe_product_id')->unique();
            $table->string('stripe_price_id');
            $table->string('stripe_price_wholesale_id');
            $table->string('stripe_price_basic_plan_id');
            $table->string('stripe_price_premium_plan_id');
            $table->integer('stock')->default(0);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
