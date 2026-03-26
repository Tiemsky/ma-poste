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
        Schema::create('cnps_requests', function (Blueprint $table) {
         $table->id();
          $table->string('reference')->unique();
          $table->foreignId('user_id')->constrained('users')->onDelete('cascade');

          // Contexte du formulaire
          $table->enum('user_type', ['particulier', 'entreprise'])->index();
          $table->foreignId('service_id')->constrained()->cascadeOnDelete();
          $table->string('service_key')->default('enrolement');

          // ÉTAPE : INFOS EMPLOYÉ / PARTICULIER
          $table->boolean('has_cnps_number')->default(false);
          $table->string('employee_cnps_number', 15)->nullable();
          $table->string('employee_last_name');
          $table->string('employee_first_name');
          $table->string('employee_nif', 20)->nullable();
          $table->date('hire_date')->nullable();

          // ÉTAPE : INFOS EMPLOYEUR
          $table->string('employer_name')->nullable();
          $table->string('employer_registration')->nullable(); // RCCM
          $table->string('employer_cnps_number', 15)->nullable();

          // ÉTAPE : CONTRAT & SALAIRE
          $table->string('contract_type'); // cdi, cdd, etc.
          $table->decimal('monthly_gross_salary', 15, 2);
          $table->string('professional_category')->nullable();


          // PIÈCES JOINTES
          $table->string('cni_recto_path')->nullable();
          $table->string('cni_verso_path')->nullable();

          // Pour d'autres documents (ex: contrat de travail, attestation)
          $table->json('additional_documents')->nullable();

          // WORKFLOW
          $table->string('status')->default('pending'); // pending, validated, rejected
          $table->timestamps();
          $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cnps_requests');
    }
};
