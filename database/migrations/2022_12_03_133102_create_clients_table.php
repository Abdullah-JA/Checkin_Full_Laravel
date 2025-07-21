<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateClientsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->increments('id');
            $table->string('FirstName', 50);
            $table->string('LastName', 50);
            $table->string('international_code');
            $table->string('Mobile');
            $table->string('Email')->nullable(true);
            $table->enum('IdType', ['ID', 'PASSPORT']);
            $table->string('IdNumber', 20);
            $table->tinyInteger('Gender')->comment('1 => Male, 2 => Female');
            $table->date('Birthday');
            $table->integer('GuestCategoryId')->unsigned()->comment('Gold, Silver, or ...');
            $table->foreign('GuestCategoryId')->references('id')->on('guestcategory');
            $table->string('Nationality', 50);
            $table->tinyInteger('GuestType')->comment('1 => Citizen, 2 => Foreigner, 3 => Gulf Citizen, 4 => Visitor Id');
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
        Schema::dropIfExists('clients');
    }
}
