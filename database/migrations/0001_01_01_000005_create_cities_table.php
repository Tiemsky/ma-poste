<?php

use App\Models\Country;
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
        Schema::create('cities', function (Blueprint $table) {
            $table->id();
            $table->string('key');
            $table->foreignIdFor(Country::class)->constrained()->cascadeOnDelete();
            $table->string('name');
            $table->string('slug')->index();
            $table->boolean('has_communes')->default(false);
            $table->timestamps();

            $table->unique(['key', 'country_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cities');
    }
};
