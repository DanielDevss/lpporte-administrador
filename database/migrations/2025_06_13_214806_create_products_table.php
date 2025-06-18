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
            $table->string('title', 120);
            $table->string('slug', 155)->unique();
            $table->string('description_short', 170)->nullable();
            $table->text('description')->nullable();
            $table->string('thumb', 255);
            $table->integer('stock')->default(0);
            $table->enum('status', ['activo', 'pausado'])->default('activo');
            $table->string('stripe_product_id')->unique();
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
