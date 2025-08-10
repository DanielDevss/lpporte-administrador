<?php

use App\Enums\OrderStatusEnum;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->string('folio', 10)->unique();
            $table->enum('status', array_column(OrderStatusEnum::cases(), 'value'))->nullable();
            $table->string('amount', 20);
            $table->enum('sold_since', ['en linea', 'externa'])->default('en linea');
            $table->string('tax', 20);
            $table->string('stripe_session_id')->unique();
            $table->string('stripe_payment_id')->unique()->nullable();
            $table->string('stripe_payment_method')->unique()->nullable();
            $table->dateTime('activated_at')->nullable();
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
