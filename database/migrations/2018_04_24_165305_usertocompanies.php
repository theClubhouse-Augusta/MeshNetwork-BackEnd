<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Usertocompanies extends Migration
{
  /**
   * Run the migrations.
   *
   * @return void
   */
  public function up()
  {

    Schema::create('usertocompanies', function (Blueprint $table) {
      $table->increments('id');
      $table->integer('userID')->unsigned();
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
    Schema::dropIfExists('usertocompanies');
  }
}
