<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class Serverdevices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        //
        Schema::create('serverdevices', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name',50);
            $table->string('buildingsIds');
            $table->string('floorsIds');
            $table->string('roomsIds');
            $table->string('token');
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
        //
        Schema::dropIfExists('serverdevices');
    }
}
