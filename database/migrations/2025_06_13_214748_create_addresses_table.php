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
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete()->cascadeOnUpdate();
            $table->string('name', 100);
            $table->string('cp', 5);
            $table->string('state', 100);
            $table->string('col', 125);
            $table->string('city', 125);
            $table->string('street', 155);
            $table->string('no_ext', 10)->nullable();
            $table->string('no_int', 10)->nullable();
            $table->string('street_ref_1', 155)->nullable();
            $table->string('street_ref_2', 155)->nullable();
            $table->string('street_ref_3', 155)->nullable();
            $table->string('street_ref_4', 155)->nullable();
            $table->string('ref_address', 355)->nullable();
            $table->boolean('main')->default(false);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};
