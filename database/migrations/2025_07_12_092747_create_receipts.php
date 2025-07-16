<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceipts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receipts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('ReceiptNumber', 50);
            $table->date('ReceiptDate');
            $table->double('Amount');
            $table->integer('EmployeeId')->unsigned();
            $table->foreign('EmployeeId')->references('id')->on('serviceemployees');
            $table->string('Description', 255)->nullable();
            $table->integer('ClientId')->unsigned()->nullable();
            $table->foreign('ClientId')->references('id')->on('clients');
            $table->integer('BookingId')->unsigned()->nullable();
            $table->foreign('BookingId')->references('id')->on('bookings');
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
        Schema::dropIfExists('receipts');
    }
}
