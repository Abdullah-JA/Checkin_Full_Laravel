<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRoomtypePricingplanTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('roomtype_pricingplan', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('roomtype_id');
            $table->foreign('roomtype_id')->references('id')->on('roomtypes')->onDelete('cascade');
            $table->unsignedInteger('pricingplan_id');
            $table->foreign('pricingplan_id')->references('id')->on('pricingplans')->onDelete('cascade');
            $table->double('DailyPrice');
            $table->double('MonthlyPrice');
            $table->double('YearlyPrice');
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
        Schema::dropIfExists('roomtype_pricingplan');
    }
}
