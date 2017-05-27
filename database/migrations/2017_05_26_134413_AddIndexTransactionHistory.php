<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Migrations\Migration;

class AddIndexTransactionHistory extends Migration
{
    const TABLE_NAME = 'transaction_history';
    const NEW_INDEX_NAME = 'transaction_history_index_object_id_user_id';

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable(self::TABLE_NAME) && !$this->isNewIndexExists()) {
            \DB::statement('COMMIT;');
            \DB::statement(
                'CREATE INDEX CONCURRENTLY ' . self::NEW_INDEX_NAME . ' ON ' . self::TABLE_NAME
                . ' (object_id, user_id)'
            );
            \DB::statement('BEGIN;');
        }
    }

    /**
     * @return bool
     */
    protected function isNewIndexExists(): bool
    {
        return (bool)$this->isIndexExists(self::TABLE_NAME, self::NEW_INDEX_NAME);
    }

    /**
     * @param string $tableName
     * @param string $indexName
     * @return bool
     */
    protected function isIndexExists(string $tableName, string $indexName): bool
    {
        $schemaBuilder = Schema::getConnection()
            ->getDoctrineSchemaManager()
            ->listTableDetails($tableName);

        return (bool)$schemaBuilder->hasIndex($indexName);
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        if (Schema::hasTable(self::TABLE_NAME) && $this->isNewIndexExists()) {
            Schema::table(self::TABLE_NAME, function ($table) {
                $table->dropIndex(self::NEW_INDEX_NAME);
            });
        }
    }
}
