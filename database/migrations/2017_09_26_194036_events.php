<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Events extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('events', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userID');
            $table->integer('spaceID');
            $table->dateTime('start');
            $table->dateTime('end');
            //$table->dateTimeTz('start'); TODO// Timezone or naa?
            //$table->dateTimeTz('end');
            $table->string('status')->default('pending'); //TODO needs approval? hence false??
            $table->string('title'); // unique? //TODO
            $table->longText('description');
            $table->string('type'); //TODO predifned values would be easier to aggredate data
            $table->longText('tags'); //TODO comma separated string?
            $table->boolean('local')->default(true);
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
        Schema::dropIfExists('events');
    }
}
