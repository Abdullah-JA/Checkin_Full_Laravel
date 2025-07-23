<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateBookingtaxesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookingtaxes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('BookingId');
            $table->foreign('BookingId')->references('id')->on('bookings');
            $table->unsignedInteger('TaxId');
            $table->foreign('TaxId')->references('id')->on('taxnames');
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
        Schema::dropIfExists('bookingtaxes');
    }
}
