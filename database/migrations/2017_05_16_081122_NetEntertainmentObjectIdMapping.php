<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

/**
 * Class NetEntertainmentObjectIdMapping
 */
class NetEntertainmentObjectIdMapping extends Migration
{
    const START_AUTOINCREMENT_VALUE = 2000000000;

    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (Schema::hasTable('netentertainment_object_id_map')) {
            return;
        }

        Schema::connection('integration')->create('netentertainment_object_id_map', function (Blueprint $table) {
            $table->bigIncrements('id')->unsigned();
            $table->integer('game_id');
            $table->string('action_id', 16);
            $table->unique(['game_id', 'action_id'], 'netentertainment_object_id_map_game_id_action_id');
        });
        \DB::statement("ALTER SEQUENCE netentertainment_object_id_map_id_seq RESTART WITH " . self::START_AUTOINCREMENT_VALUE . ";");
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('netentertainment_object_id_map', function (Blueprint $table) {
            $table->dropUnique('netentertainment_object_id_map_game_id_action_id');
        });
        Schema::connection('integration')->dropIfExists('netentertainment_object_id_map');
    }
}
