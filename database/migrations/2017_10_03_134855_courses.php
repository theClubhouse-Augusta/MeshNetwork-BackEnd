<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Courses extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function(Blueprint $table) {
          $table->increments('id');
          $table->string('title');
          $table->longtext('description');
          $table->longtext('banner');
          $table->dateTime('startDate');
          $table->dateTime('endDate');
          $table->boolean('ongoing')->default(0);
          $table->string('price');
          $table->string('instructor');
          $table->integer('userID');
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
        //
    }
}
