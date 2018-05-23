<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Companies extends Migration
{
  /**
   * Run the migrations.
   * @return void
   */
  public function up()
  {
    Schema::create('companies', function (Blueprint $table) {
      $table->increments('id')->unsigned();
      $table->integer('userID')->unsigned();
      $table->string('name');
      $table->string('email')->nullable();
      $table->string('address');
      $table->string('city');
      $table->string('state');
      $table->string('zipcode');
      $table->string('url');
      $table->string('description')->nullable();
      $table->integer('employeeCount')->unsigned()->nullable();
      $table->string('logo')->nullable();
      $table->dateTime('foundingDate')->nullable();
      $table->string('facebook')->nullable();
      $table->string('instagram')->nullable();
      $table->string('pinterest')->nullable();
      $table->string('linkedin')->nullable();
      $table->string('discord')->nullable();
      $table->string('snapchat')->nullable();
      $table->string('youtube')->nullable();
      $table->string('twitter')->nullable();
    });
  }

  /**
   * Reverse the migrations.
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('companies');
  }
}
