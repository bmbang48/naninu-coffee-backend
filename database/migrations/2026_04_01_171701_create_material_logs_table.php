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
        Schema::create('material_logs', function (Blueprint $table) {
            $table->id();

            $table->foreignId('material_id')
                ->constrained('materials')
                ->onDelete('cascade');

            $table->enum('type', ['IN', 'OUT']); // masuk / keluar
            $table->integer('amount'); // jumlah perubahan

            $table->string('note')->nullable(); // contoh: "kopi tumpah", "restock"

            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('material_logs');
    }
};
