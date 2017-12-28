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
            $table->integer('roleID');
            $table->integer('spaceID');
            $table->string('email')->unique();
            $table->string('password');
            $table->string('title')->nullable(); 
            $table->boolean('hireable')->default(false);
            $table->boolean('searchOpt')->default(false);
            $table->string('company')->nullable();
            $table->string('website')->nullable();
            $table->longText('bio')->nullable();
            $table->longText('avatar')->nullable();
            $table->boolean('ban')->default(false);
            $table->boolean('verified')->default(false);
            $table->boolean('subscriber')->default(false);
            $table->integer('score')->default(100);
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
