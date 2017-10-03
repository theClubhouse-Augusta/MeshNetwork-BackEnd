<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Questions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('questions', function(Blueprint $table) {
        $table->increments('id');
        $table->longtext('content');
        $table->string('type');
        $table->longtext('options');
        $table->integer('answer')->nullable();
        $table->integer('quizID');
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
