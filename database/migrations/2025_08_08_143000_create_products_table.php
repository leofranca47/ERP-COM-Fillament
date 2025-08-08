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
        Schema::create('products', function (Blueprint $table) {
            $table->id();

            // Basic Information
            $table->string('code');
            $table->string('description');
            $table->decimal('gross_weight', 10, 3)->nullable();
            $table->decimal('net_weight', 10, 3)->nullable();
            $table->string('brand')->nullable();
            $table->string('unit_of_measure');
            $table->foreignId('product_group_id')->constrained()->onDelete('restrict');
            $table->foreignId('product_subgroup_id')->constrained()->onDelete('restrict');
            $table->string('image_path')->nullable();
            $table->boolean('active')->default(true);

            // Stock and Prices
            $table->decimal('sale_price', 10, 2)->default(0);
            $table->decimal('average_cost', 10, 2)->default(0);
            $table->decimal('current_cost', 10, 2)->default(0);
            $table->decimal('minimum_stock', 10, 2)->default(0);
            $table->decimal('maximum_stock', 10, 2)->default(0);

            // Grading information will be in a separate table

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('products');
    }
};
