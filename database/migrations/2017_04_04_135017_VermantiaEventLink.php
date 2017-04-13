<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class VermantiaEventLink extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::connection('integration')->create('vermantia_event_link', function (Blueprint $table) {
            $table->bigInteger('event_id_vermantia', false, true);
            $table->bigInteger('event_id', false, true);

            $table->primary('event_id_vermantia');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::connection('integration')->dropIfExists('vermantia_event_link');
    }
}
