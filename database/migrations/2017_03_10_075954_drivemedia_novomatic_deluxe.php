<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class DrivemediaNovomaticDeluxe extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('integration')->create('drivemedia_novomatic_deluxe',//
            function (Blueprint $table) {
                $table->bigIncrements('id')->unsigned();
                $table->string('betInfo');
                $table->string('matrix');
                $table->mediumText('packet');
                $table->float('bet');
                $table->float('winLose');
                $table->bigInteger('parent_id')->unsigned();

                DB::connection()->getPdo()->exec('CREATE SEQUENCE IF NOT EXISTS common_integration_serial START 1');
            });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('drivemedia_novomatic_deluxe',
            function (Blueprint $table) {
                DB::connection()->getPdo()->exec('DROP SEQUENCE IF EXISTS common_integration_serial ;');
            });
        Schema::connection('integration')->dropIfExists('drivemedia_novomatic_deluxe_object_id_map');
    }
}
