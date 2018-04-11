<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Events extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userID');
            $table->integer('spaceID');
            $table->boolean('multiday');
            $table->string('status')->default('pending'); //TODO needs approval? hence false??
            $table->string('title'); // unique? //TODO
            $table->string('image')->nullable(); // unique? //TODO
            $table->text('description');
            $table->string('address')->nullable();
            $table->string('city')->nullable();
            $table->string('state')->nullable();
            $table->string('zipcode')->nullable();
            $table->float('lat')->nullable();
            $table->float('lon')->nullable();
            $table->string('url')->nullable();
            $table->tinyInteger('challenge')->nullable();
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
        Schema::dropIfExists('events');
    }
}
