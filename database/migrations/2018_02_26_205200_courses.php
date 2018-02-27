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
        Schema::create('courses', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userID');
            $table->integer('categoryID');
            $table->string('courseName')->nullable();
            $table->longText('courseSummary')->nullable();
            $table->longText('courseInformation')->nullable();
            $table->longText('courseImage')->nullable();
            $table->longText('courseThumbnail')->nullable();
            $table->string('courseInstructorName')->nullable();
            $table->longText('courseInstructorInfo')->nullable();
            $table->longText('courseInstructorAvatar')->nullable();
            $table->boolean('courseFeatured')->default(0);
            $table->string('courseStatus')->default('Draft');
            $table->integer('coursePrice')->nullable()->default(0);
            $table->longText('courseVideo')->nullable();
            $table->boolean('archive')->default(false);
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
        Schema::drop('courses');
    }
}
