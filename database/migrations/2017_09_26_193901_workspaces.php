<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Workspaces extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('workspaces', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userID');
            $table->string('name'); // unique? //TODO
            $table->string('city');
            $table->string('address');
            $table->string('state');
            $table->integer('zipcode');
            $table->string('email')->unique();
            $table->string('website');
            $table->integer('phone_number');
            $table->longText('description');
            //TODO: should we insert a generic logo deafult
            // if they don't provide one for some reason?
            $table->longtext('logo')->nullable();
            $table->string('status')->default('pending'); //approved, declined
            $table->string('organizers');
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
        Schema::dropIfExists('workspaces');
    }
}
