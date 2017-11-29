<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Appearances extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('appearances', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userID');
            $table->integer('spaceID');
            $table->integer('eventID')->nullable();
            $table->string('occasion')->nullable();
            //   other options: 'work', 'event',booking', 'invite', 'student', 'teacher' 
            //TODO: if eventID is not null then work = false?
            // $table->boolean('work')->default(true);
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
        Schema::dropIfExists('appearances');
    }
}
