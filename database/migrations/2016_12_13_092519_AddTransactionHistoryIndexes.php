<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddTransactionHistoryIndexes extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transaction_history', function (Blueprint $table) {
            $table->index('service_id', 'index_op_service_id');
            $table->index('transaction_type', 'index_op_transaction_type');
            $table->index('partner_id', 'index_op_partner_id');
            $table->index('status', 'index_op_status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transaction_history', function (Blueprint $table) {
            $table->dropIndex('index_op_service_id');
            $table->dropIndex('index_op_transaction_type');
            $table->dropIndex('index_op_partner_id');
            $table->dropIndex('index_op_status');
        });
    }
}
