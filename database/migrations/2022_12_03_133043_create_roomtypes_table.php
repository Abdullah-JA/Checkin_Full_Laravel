<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomtypesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roomtypes', function (Blueprint $table) {
            $table->increments('id');
            $table->string('NameAr');
            $table->string('NameEn');
            $table->double("MaxDailyPrice");
            $table->double("MinDailyPrice");
            $table->double("MaxMonthlyPrice");
            $table->double("MinMonthlyPrice");
            $table->double("MaxYearlyPrice");
            $table->double("MinYearlyPrice");
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
        Schema::dropIfExists('roomtypes');
    }
}
