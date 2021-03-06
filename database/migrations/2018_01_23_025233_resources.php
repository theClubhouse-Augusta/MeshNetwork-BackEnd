<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Resources extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('resources', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('spaceID');
            $table->string('resourceName');
            $table->string('resourceEmail')->nullable();
            $table->string('resourceDays');
            $table->string('resourceStartTime');
            $table->string('resourceEndTime');
            $table->tinyInteger('resourceAvailable')->default(1);
            $table->integer('resourceIncrement')->default(60);
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
        Schema::drop('resources');
    }
}
