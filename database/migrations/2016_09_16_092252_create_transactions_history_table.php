<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTransactionsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('local_test')->create('transaction_history', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('operation_id');
            $table->integer('service_id');
            $table->integer('operation_id');
            $table->integer('amount')->default(0);
            $table->smallInteger('move');
            $table->integer('partner_id');
            $table->integer('cashdesk');
            $table->string('status');
            $table->string('currency');
            $table->string('foreign_id');
            $table->string('transaction_type');
            $table->timestamps();

            $table->unique('operation_id');
            $table->index('operation_id', 'index_op_id');
            $table->index('user_id', 'user_id_op_id');
            $table->index('foreign_id', 'foreign_id_op_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('local_test')->dropIfExists('transaction_history');
    }
}
