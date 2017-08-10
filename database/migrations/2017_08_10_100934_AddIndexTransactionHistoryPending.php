<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

/**
 * Class AddIndexTransactionHistoryPending
 */
class AddIndexTransactionHistoryPending extends Migration
{
    const TABLE_NAME = 'transaction_history';
    const INDEX_NAME = 'transaction_history_index_status_pending';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable(self::TABLE_NAME)) {
            \DB::statement('COMMIT;');
            \DB::statement(
                'CREATE INDEX CONCURRENTLY IF NOT EXISTS ' . self::INDEX_NAME . ' ON ' . self::TABLE_NAME
                . ' (status) WHERE status = \'pending\''
            );
            \DB::statement('BEGIN;');
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable(self::TABLE_NAME)) {
            \DB::statement(
                'DROP INDEX CONCURRENTLY IF EXISTS ' . self::INDEX_NAME
            );
        }
    }
}
