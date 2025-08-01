<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateFacilitysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facilitys', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('BuildingId')->unsigned();
            $table->foreign('BuildingId')->references('id')->on('buildings');
            $table->integer('FloorId')->unsigned();
            $table->foreign('FloorId')->references('id')->on('floors');
            $table->integer('TypeId', false, true);
            $table->string('TypeName');
            $table->string('Name');
            $table->integer('Control', false, true);
            $table->string('photo');
            $table->string('Description', 100);
            $table->text("LockId");
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
        Schema::dropIfExists('facilitys');
    }
}
