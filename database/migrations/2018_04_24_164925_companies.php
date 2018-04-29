<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Companies extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('companies', function (Blueprint $table) {
      $table->increments('id')->unsigned();
      $table->integer('userID')->unsigned();
      $table->string('description');
      $table->integer('employeeCount')->unsigned();
      $table->string('logo');
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
    Schema::dropIfExists('companies');
  }
}
