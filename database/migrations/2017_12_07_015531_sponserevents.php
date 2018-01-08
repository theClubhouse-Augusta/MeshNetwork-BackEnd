<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Sponserevents extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sponserevents', function (Blueprint $table) 
        {
            $table->increments('id');
            $table->integer('eventID')->unsigned();
            $table->integer('sponserID')->unsigned();
            $table->timestamps();
            $table->foreign('eventID')->references('id')->on('events')->onDelete('cascade');
            $table->foreign('sponserID')->references('id')->on('sponsers')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sponserevents');
    }
}
