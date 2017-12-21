<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Kiosks extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kiosks', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('spaceID');
            $table->string('inputPlaceholder');
            $table->longText('logo')->nullable();
            $table->string('primaryColor');
            $table->string('secondaryColor');
            $table->string('userWelcome');
            $table->string('userThanks');
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
       Schema::dropIfExists('kiosks');
    }
}
