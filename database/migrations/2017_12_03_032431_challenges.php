<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Challenges extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('challenges', function (Blueprint $table) {
          $table->increments('id');
          $table->integer('spaceID');
          $table->longText('challengeImage');
          $table->string('challengeTitle');
          $table->string('challengeSlug');
          $table->longText('challengeContent');
          $table->string('startDate')->nullable();
          $table->string('endDate')->nullable();
          $table->string('status')->default('Pending');
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
      Schema::drop('challenges');
    }
}
