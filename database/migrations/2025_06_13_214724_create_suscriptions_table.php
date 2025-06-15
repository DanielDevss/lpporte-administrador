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
        Schema::create('suscriptions', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100)->unique();
            $table->string('amount', 25);
            $table->boolean('free')->default(false);
            $table->string('stripe_price_id')->unique();
            $table->string('stripe_product_id')->unique();
            $table->timestamps();
        });

        Schema::create('suscription_attributes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('suscription_id')->constrained('suscriptions')->cascadeOnDelete()->cascadeOnUpdate();
            $table->integer('position')->nullable();
            $table->string('text');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('suscription_attributes');
        Schema::dropIfExists('suscriptions');
    }
};
