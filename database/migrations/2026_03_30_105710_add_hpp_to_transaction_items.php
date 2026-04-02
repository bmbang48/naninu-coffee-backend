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
        Schema::table('transaction_items', function (Blueprint $table) {
            //
            $table->decimal('hpp', 12, 2)->after('quantity');
            $table->decimal('subtotal_hpp', 12, 2)->after('hpp');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('transaction_items', function (Blueprint $table) {
            //
            $table->dropColumn(['hpp', 'subtotal_hpp']);
        });
    }
};
