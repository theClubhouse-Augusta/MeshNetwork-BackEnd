<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Companytoverticals extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {
    Schema::create('companytoverticals', function (Blueprint $table) {
      $table->increments('id')->unsigned();
      $table->integer('verticalID')->unsigned();
      $table->integer('companyID')->unsigned();
      $table->timestamps();
      $table->foreign('companyID')
        ->references('id')
        ->on('companies')
        ->onDelete('cascade');
    });
  }

  /**
   * Reverse the migrations.
   *
   * @return void
   */
  public function down()
  {
    Schema::dropIfExists('companytoverticals');
  }
}
