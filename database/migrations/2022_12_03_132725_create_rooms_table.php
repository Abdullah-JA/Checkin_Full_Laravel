<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('rooms', function (Blueprint $table) {
            $table->increments('id'); //1
            $table->integer('RoomNumber', false, true); //2
            $table->integer('Status', false, true)->default(0); //3
            $table->integer('Building', false, true); //5
            $table->integer('building_id', false, true); //6
            $table->foreign('building_id')->references('id')->on('buildings'); //7
            $table->integer('Floor', false, true); //8
            $table->integer('floor_id', false, true); //9
            $table->foreign('floor_id')->references('id')->on('floors'); //10
            $table->integer('RoomTypeId'); //11
            $table->foreign('RoomTypeId')->references('id')->on('roomtypes');
            $table->integer('SuiteStatus', false, true)->default(0); //12
            $table->integer('SuiteNumber', false, true)->default(0); //13
            $table->integer('SuiteId', false, true)->default(0); //14
            $table->integer('ReservationNumber', false, true)->default(0); //15
            $table->integer('roomStatus', false, true)->default(1); //16
            $table->string('token')->default(''); //55
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
        Schema::dropIfExists('rooms');
    }
}
