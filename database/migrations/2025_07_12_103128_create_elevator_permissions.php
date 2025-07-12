<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateElevatorPermissions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('elevator_permissions', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('elevator_id')->unsigned();
            $table->foreign('elevator_id')->references('id')->on('elevators');
            $table->integer('permission_id')->unsigned();
            $table->foreign('permission_id')->references('id')->on('elevator_floors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('elevator_permissions');
    }
}
