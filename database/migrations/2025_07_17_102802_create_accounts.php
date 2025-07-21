<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->integer('ClientId')->unsigned();
            $table->foreign('ClientId')->references('id')->on('clients');
            $table->tinyInteger('type')->comment('1 => invoice, 2 => receipts');
            $table->integer("Number");
            $table->double("Amount");
            $table->date("Date");
            $table->time("Time");
            $table->integer('UserId')->unsigned();
            $table->foreign('UserId')->references('id')->on('serviceemployees');
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
        Schema::dropIfExists('accounts');
    }
}
