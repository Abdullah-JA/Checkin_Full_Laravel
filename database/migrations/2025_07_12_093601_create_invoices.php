<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoices extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
public function up()
{
    Schema::create('invoices', function (Blueprint $table) {
        $table->increments('id');
        $table->string('InvoiceNumber', 50);
        $table->date('InvoiceDate');
        $table->double('TotalAmount');
        $table->double('Discount')->default(0);
        $table->double('Tax')->default(0);
        $table->double('FinalAmount');
        $table->integer('ClientId')->unsigned();
        $table->foreign('ClientId')->references('id')->on('clients');
        $table->integer('BookingId')->unsigned()->nullable();
        $table->foreign('BookingId')->references('id')->on('bookings');
        $table->string('Description', 255)->nullable();
        $table->integer('CreatedBy')->unsigned();
        $table->foreign('CreatedBy')->references('id')->on('serviceemployees');
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
        Schema::dropIfExists('invoices');
    }
}
