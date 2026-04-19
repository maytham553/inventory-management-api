<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sale_product', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->unique(['sale_id', 'product_id'], 'sale_product_unique');
        });

        Schema::table('purchase_raw_material', function (Blueprint $table) {
            $table->dropSoftDeletes();
            $table->unique(['purchase_id', 'raw_material_id'], 'purchase_raw_material_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sale_product', function (Blueprint $table) {
            $table->dropUnique('sale_product_unique');
            $table->softDeletes();
        });

        Schema::table('purchase_raw_material', function (Blueprint $table) {
            $table->dropUnique('purchase_raw_material_unique');
            $table->softDeletes();
        });
    }
};
