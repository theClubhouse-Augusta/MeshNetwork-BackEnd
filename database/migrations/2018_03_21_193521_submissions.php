<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class Submissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('submissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('userID');
            $table->integer('challengeID');
            $table->mediumText('submissionTitle');
            $table->longText('submissionDescription');
            $table->mediumText('submissionGithub')->nullable();
            $table->mediumText('submissionVideo')->nullable();
            $table->mediumText('submissionFile')->nullable();
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
        Schema::drop('submissions');
    }
}
