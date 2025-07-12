<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use phpseclib3\Math\BinaryField\Integer;

class CreateBookingsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('bookings', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('RoomNumber', false, true);
            $table->integer('ClientId', false, true);
            $table->integer('Status', false, true);
            $table->integer('RoomOrSuite', false, true);
            $table->integer('MultiRooms', false, true);
            $table->string('AddRoomNumber', 100);
            $table->string('AddRoomId', 100);
            $table->date('StartDate');
            $table->integer('Nights', false, true);
            $table->date('EndDate');
            $table->integer('BuildingNo', false, true);
            $table->integer('Floor', false, true);
            $table->string('ClientFirstName', 40);
            $table->string('ClientLastName', 40);
            $table->enum('IdType', ['ID', 'PASSPORT']);
            $table->integer('IdNumber', false, true);
            $table->integer('MobileNumber', false, true);
            $table->string('Email', 100);
            $table->float('Rating', 1, 1);
            $table->tinyInteger("GuestControl")->default(0);
            $table->string("password", 255);
            $table->tinyInteger("BookingType")->comment("1=> Single 2=> collective");
            $table->tinyInteger("BookingStatus")->comment("1=> Confirmed 2=> Unconfirmed");
            $table->integer('StayReasonId');
            $table->foreign('StayReasonId')->references('id')->on('stayreason');
            $table->integer('BookingSourceId');
            $table->foreign('BookingSourceId')->references('id')->on('bookingsource');
            $table->tinyInteger("RentType")->comment("1=>daily 2=>monthly 3=>annual");
            $table->double('BasePrice');
            $table->double('Discount');
            $table->double('Extras');
            $table->double('Penalties');
            $table->double('SubTotal');
            $table->double('Taxes');
            $table->double('Total');
            $table->longText('FacilityIds');
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
        Schema::dropIfExists('bookings');
    }
}
