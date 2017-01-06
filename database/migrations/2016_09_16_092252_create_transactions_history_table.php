<?php


use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

class CreateTransactionsHistoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('integration')->create('transaction_history', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->integer('user_id')->unsigned();
            $table->integer('operation_id')->unsigned();
            $table->integer('service_id')->unsigned();
            $table->integer('amount')->default(0);
            $table->smallInteger('move')->unsigned();
            $table->integer('partner_id');
            $table->integer('cashdesk');
            $table->char('status', 16);
            $table->char('currency', 16);
            $table->string('foreign_id');
            $table->char('transaction_type', 16);
            $table->bigInteger('object_id');
            $table->timestamps();

            $table->unique('operation_id');

            $table->index(['service_id', 'partner_id', 'user_id'], 'transaction_history_index_service_partner_user');
            $table->index(['created_at', 'user_id'], 'transaction_history_index_created_at_user_id');
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
            $table->dropIndex('transaction_history_index_service_partner_user');
            $table->dropIndex('transaction_history_index_created_at_user_id');
        });

        Schema::connection('integration')->dropIfExists('transaction_history');
    }
}