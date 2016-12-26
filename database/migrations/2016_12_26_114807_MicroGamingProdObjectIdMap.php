<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class MicroGamingProdObjectIdMap extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('integration')->create('microgaming_prod_object_id_map', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id');
            $table->string('currency');
            $table->bigInteger('game_id');

            $table->index('user_id', 'microgaming_prod_object_id_user_id');
            $table->index('currency', 'microgaming_prod_object_id_currency');
            $table->index('game_id', 'microgaming_prod_object_id_game_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('microgaming_prod_object_id_map', function (Blueprint $table) {
            $table->dropIndex('microgaming_prod_object_id_user_id');
            $table->dropIndex('microgaming_prod_object_id_currency');
            $table->dropIndex('microgaming_prod_object_id_game_id');
        });

        Schema::connection('integration')->dropIfExists('microgaming_prod_object_id_map');
    }
}
