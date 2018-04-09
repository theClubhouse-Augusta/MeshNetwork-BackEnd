<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Opts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opts', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('spaceID');
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
        Schema::dropIfExists('opts');
    }
}
