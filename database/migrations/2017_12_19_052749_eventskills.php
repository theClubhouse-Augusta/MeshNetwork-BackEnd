<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Eventskills extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('eventskills', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('eventID')->unsigned();
            $table->integer('skillID');
            $table->string('name');
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
        Schema::dropIfExists('eventskills');
    }
}
