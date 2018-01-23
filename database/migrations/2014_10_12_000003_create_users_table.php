<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('roleID');
            $table->string('email')->unique();
            $table->string('password');
            $table->integer('spaceID')->nullable();
            $table->string('title')->nullable();
            $table->string('phoneNumber')->nullable();
            $table->text('bio')->nullable();
            $table->mediumText('avatar')->nullable();
            $table->string('facebook')->nullable();
            $table->string('twitter')->nullable();
            $table->string('instagram')->nullable();
            $table->string('linkedin')->nullable();
            $table->string('github')->nullable();
            $table->string('behance')->nullable();
            $table->boolean('ban')->default(false);
            $table->boolean('subscriber')->default(false);
            $table->text('skills')->nullable();
            $table->rememberToken();
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
        Schema::dropIfExists('users');
    }
}
