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
    Schema::create('administrations', function (Blueprint $table) {
              $table->id();

              $table->string('key')->unique();

              $table->string('name');
              $table->string('slug')->unique();

              $table->string('logo')->nullable();
              $table->text('description')->nullable();

              $table->foreignId('country_id')->constrained()->cascadeOnDelete();
              $table->foreignId('city_id')->nullable()->constrained()->nullOnDelete();
              $table->foreignId('commune_id')->nullable()->constrained()->nullOnDelete();

              $table->string('status')->default('disable');

              $table->timestamps();

              $table->index(['country_id', 'city_id', 'commune_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('administrations');
    }
};
