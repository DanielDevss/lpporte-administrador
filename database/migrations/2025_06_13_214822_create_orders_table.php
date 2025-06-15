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
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->string('folio', 10)->unique();
            $table->enum('status', ['pendiente', 'procesando', 'pagado', 'cancelado', 'error'])->default('pendiente');
            $table->string('amount', 20);
            $table->enum('sold_since', ['en linea', 'externa'])->default('en linea');
            $table->string('tax', 20);
            $table->string('stripe_payment_id');
            $table->string('stripe_payment_method');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};
