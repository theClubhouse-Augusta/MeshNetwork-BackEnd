<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Eventdates extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventdates', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('eventID')->unsigned();
            $table->dateTime('start');
            $table->dateTime('end');
            $table->timestamps();
            $table->foreign('eventID')->references('id')->on('events')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('eventdates');
    }
}
