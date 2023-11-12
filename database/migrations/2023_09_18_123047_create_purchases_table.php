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
        Schema::create('purchases', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->constrained();
            $table->foreignId('supplier_id')->constrained();
            $table->foreignId('supplier_transaction_id')->nullable()->constrained()->onDelete('cascade');
            $table->decimal('subtotal_amount', 10 );
            $table->decimal('total_amount', 10 );
            $table->decimal('discount_amount', 10 ) -> nullable();
            $table->decimal('discount_percentage', 5 ) -> nullable();
            $table->enum('status', ['pending', 'confirmed', 'cancelled'])->default('pending');
            $table->text('note')->nullable();          
            $table->softDeletes();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('purchases');
    }
};
