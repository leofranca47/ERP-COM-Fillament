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
            $table->foreignId('person_id')->constrained()->onDelete('cascade');
            $table->enum('type', ['residential', 'commercial'])->comment('Tipo de Endereço');
            $table->string('zip_code')->comment('CEP');
            $table->foreignId('city_id')->constrained()->comment('Cidade');
            $table->string('state', 2)->comment('Estado');
            $table->string('street')->comment('Logradouro');
            $table->string('number')->comment('Número');
            $table->string('neighborhood')->comment('Bairro');
            $table->string('complement')->nullable()->comment('Complemento');
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
