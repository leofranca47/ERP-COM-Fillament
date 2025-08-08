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
        Schema::create('people', function (Blueprint $table) {
            $table->id();
            $table->enum('registration_type', ['client', 'supplier'])->comment('Tipo de Cadastro');
            $table->enum('person_type', ['individual', 'legal'])->comment('Tipo de Pessoa (Física/Jurídica)');
            $table->date('birth_date')->nullable()->comment('Data de Nascimento/Criação');
            $table->string('name')->comment('Nome/Nome Fantasia');
            $table->string('company_name')->nullable()->comment('Razão Social');
            $table->string('state_registration')->nullable()->comment('Inscrição Estadual');
            $table->string('document')->comment('CPF/CNPJ');
            $table->enum('status', ['active', 'inactive'])->default('active')->comment('Situação');
            $table->string('profile_photo')->nullable()->comment('Foto do Perfil');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('people');
    }
};
