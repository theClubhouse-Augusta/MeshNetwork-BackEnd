<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Usertosocialmedias extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('usertosocialmedias', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userID');
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
    public function down() {
        Schema::dropIfExists('usertosocialmedias');
    }
}
