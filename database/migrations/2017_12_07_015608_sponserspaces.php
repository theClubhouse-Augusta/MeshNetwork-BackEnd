<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Sponserspaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sponserspaces', function (Blueprint $table) 
        {
            $table->increments('id');
            $table->integer('spaceID')->unsigned();
            $table->integer('sponserID')->unsigned();
            $table->timestamps();
            $table->foreign('spaceID')->references('id')->on('workspaces')->onDelete('cascade');
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
        Schema::dropIfExists('sponserspaces');
    }
}
