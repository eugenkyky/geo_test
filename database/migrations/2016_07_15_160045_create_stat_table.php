<?php

use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateStatTable extends Migration
{
    public function up()
    {

        Schema::create('country', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('count')->unsigned()->default(0);
        });

        Schema::create('city', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->integer('country_id')->unsigned();
            $table->foreign('country_id')->references('id')->on('country');
        });

        Schema::create('order', function (Blueprint $table) {
            $table->increments('id');
            $table->string('text');
            $table->integer('country_id')->unsigned();
            $table->foreign('country_id')->references('id')->on('country');
            $table->integer('city_id')->unsigned();
            $table->foreign('city_id')->references('id')->on('city');
        });
    }

    public function down()
    {
        Schema::drop('country');
        Schema::drop('city');
        Schema::drop('order');
    }
}
