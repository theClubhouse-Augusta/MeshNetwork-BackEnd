<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Bookings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userID');
            $table->integer('spaceID');
            $table->integer('bookablesID');
            $table->dateTime('start');
            $table->dateTime('end');
            //$table->dateTimeTz('start'); TODO// Timezone or naa?
            //$table->dateTimeTz('end');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('bookings');

    }
}
