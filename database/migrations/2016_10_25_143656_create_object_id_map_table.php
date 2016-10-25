<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateObjectIdMapTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('integration')->create('integration.object_id_map', function (Blueprint $table) {
            $table->increments('id');
            $table->string('object_id');
            $table->integer('service_id');

            $table->index('object_id', 'index_object_id');
            $table->index('service_id', 'index_service_id');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('integration')->dropIfExists('integration.object_id_map');
    }
}
