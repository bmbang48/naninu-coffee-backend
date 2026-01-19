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
        Schema::create('recipe_products', function (Blueprint $table) {
            $table->id();
            $table->foreignId('id_product')->references('id')->on('products')->onDelete('cascade');
            $table->foreignId('id_material')->references('id')->on('materials')->onDelete('cascade');
            $table->integer('amount_used');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipe_products');
    }
};
