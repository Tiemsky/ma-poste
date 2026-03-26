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
        Schema::create('customs_items', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customs_request_id')->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('category')->index();
            $table->integer('quantity');
            $table->decimal('unit_value', 15, 2);
            $table->decimal('total_value', 15, 2);
            $table->string('origin_country');
            $table->string('weight')->nullable(); // Ex: "2kg" ou on pourrait le séparer en value/unit
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customs_items');
    }
};
