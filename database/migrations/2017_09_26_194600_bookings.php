<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Bookings extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->string('email');
            $table->integer('spaceID')->unsigned();
            $table->integer('resourceID');
            $table->dateTime('start');
            $table->dateTime('end');
            /*$table->string('day');
            $table->string('time');*/
            $table->string('token');
            $table->string('status')->default('pending');
            $table->timestamps();
            $table->foreign('spaceID')->references('id')->on('workspaces')->onDelete('cascade');
        });

    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down() {
        Schema::dropIfExists('bookings');
    }
}
