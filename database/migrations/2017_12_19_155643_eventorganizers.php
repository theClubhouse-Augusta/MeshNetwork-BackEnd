<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Eventorganizers extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventorganizers', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userID');
            $table->integer('eventID')->unsigned();
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
        Schema::dropIfExists('eventorganizers');
    }
}
