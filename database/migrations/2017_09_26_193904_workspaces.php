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
            $table->string('name');
            $table->string('city');
            $table->string('address');
            $table->string('state');
            $table->integer('zipcode');
            $table->float('lon')->nullable();
            $table->float('lat')->nullable();
            $table->string('email');
            $table->string('website');
            $table->string('phone_number');
            $table->longText('description');
            $table->longtext('logo')->nullable();
            $table->string('status')->default('pending'); //approved, declined
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
