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
        Schema::table('sales', function (Blueprint $table) {
            $table->index(['status', 'updated_at', 'id'], 'sales_status_updated_at_id_idx');
            $table->index(['customer_id', 'id'], 'sales_customer_id_id_idx');
        });

        Schema::table('customer_transactions', function (Blueprint $table) {
            $table->index(['customer_id', 'created_at'], 'cust_tx_customer_id_created_at_idx');
            $table->index(['updated_at', 'id'], 'cust_tx_updated_at_id_idx');
        });

        Schema::table('supplier_transactions', function (Blueprint $table) {
            $table->index(['supplier_id', 'created_at'], 'supp_tx_supplier_id_created_at_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->index(['updated_at', 'id'], 'expenses_updated_at_id_idx');
            $table->index(['user_id', 'id'], 'expenses_user_id_id_idx');
        });

        Schema::table('raw_material_withdrawal_records', function (Blueprint $table) {
            $table->index(['raw_material_id', 'id'], 'rm_withdraw_raw_material_id_id_idx');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('sales', function (Blueprint $table) {
            $table->dropIndex('sales_status_updated_at_id_idx');
            $table->dropIndex('sales_customer_id_id_idx');
        });

        Schema::table('customer_transactions', function (Blueprint $table) {
            $table->dropIndex('cust_tx_customer_id_created_at_idx');
            $table->dropIndex('cust_tx_updated_at_id_idx');
        });

        Schema::table('supplier_transactions', function (Blueprint $table) {
            $table->dropIndex('supp_tx_supplier_id_created_at_idx');
        });

        Schema::table('expenses', function (Blueprint $table) {
            $table->dropIndex('expenses_updated_at_id_idx');
            $table->dropIndex('expenses_user_id_id_idx');
        });

        Schema::table('raw_material_withdrawal_records', function (Blueprint $table) {
            $table->dropIndex('rm_withdraw_raw_material_id_id_idx');
        });
    }
};
