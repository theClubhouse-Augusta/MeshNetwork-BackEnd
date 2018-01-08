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
            $table->float('lon');
            $table->float('lat');
            $table->string('email');
            $table->string('website');
            $table->string('phone_number');
            $table->text('description');
            $table->mediumText('logo')->nullable();
            $table->string('status')->default('pending'); //approved, declined
            $table->string('stripe')->nullable(); 
            $table->string('facebook')->nullable(); 
            $table->string('twitter')->nullable(); 
            $table->string('instagram')->nullable(); 
            $table->string('linkedin')->nullable(); 
            $table->string('github')->nullable(); 
            $table->string('dribble')->nullable(); 
            $table->string('behance')->nullable(); 
            $table->string('angellist')->nullable(); 
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
