<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInvoicereturns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('invoicereturns', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('InvoiceId')->unsigned();
            $table->foreign('InvoiceId')->references('id')->on('invoices');
            $table->date('ReturnDate');
            $table->double('ReturnAmount');
            $table->string('Reason', 255)->nullable();
            $table->text('Notes')->nullable();
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
        Schema::dropIfExists('invoicereturns');
    }
}
