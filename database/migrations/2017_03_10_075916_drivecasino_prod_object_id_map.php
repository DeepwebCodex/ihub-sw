<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DrivecasinoProdObjectIdMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('integration')->create('drivecasino_prod_object_id_map', function (Blueprint $table) {
            $table->bigInteger('id');
            $table->string('trade_id', 32);
            $table->index(['trade_id'], 'drivecasino_prod_object_id_map_trade_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('integration')->drop('drivecasino_prod_object_id_map');
        Schema::table('drivecasino_prod_object_id_map', function (Blueprint $table) {
            $table->dropIndex('drivecasino_prod_object_id_map_trade_id');
        });
        Schema::connection('integration')->dropIfExists('drivecasino_prod_object_id_map');
    }
}
