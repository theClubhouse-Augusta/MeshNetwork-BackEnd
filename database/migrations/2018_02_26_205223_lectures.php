<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Lectures extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('lectures', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('lessonID');
            $table->string('lectureName');
            $table->longText('lectureContent')->nullable();
            $table->string('lectureType');
            $table->longText('lectureVideo')->nullable();
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
        Schema::drop('lectures');
    }
}
