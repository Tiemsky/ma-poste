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
        Schema::create('customs_requests', function (Blueprint $table) {
            $table->id();
            $table->string('key')->unique();
            $table->string('reference')->unique();
            $table->foreignId('user_id')->constrained()->cascadeOnDelete();
            $table->foreignId('service_id')->constrained()->cascadeOnDelete();
            $table->string('declarant_type')->nullable();
            $table->string('declarant_name')->nullable();
            $table->string('declarant_id_number')->nullable();
            // Section 1 : Transport
            $table->enum('transport_mode', ['air', 'sea', 'road']);
            $table->string('departure_country');
            $table->string('arrival_country');
            $table->string('transport_company');
            $table->string('tracking_number')->nullable()->index();

            // Section 3 : Documents
            $table->string('invoice_path')->nullable();


             // PIÈCES JOINTES
            $table->string('cni_recto')->nullable();
            $table->string('cni_verso')->nullable();

            $table->string('status')->default('pending'); // pending, approved, rejected
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('customs_requests');
    }
};
