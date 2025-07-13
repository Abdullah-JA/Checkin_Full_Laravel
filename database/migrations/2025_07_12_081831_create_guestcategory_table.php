<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestcategoryTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guestcategory', function (Blueprint $table) {
            $table->increments('id');
            $table->string('NameCategory', 50);
            $table->tinyInteger('DiscountType')->comment('1=>ConstantValue 2=>DiscountRates 3=>Other');
            $table->double('DiscountValue')->default(0);
            $table->longText('OtherFeaturesIds');
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
        Schema::dropIfExists('guestcategory');
    }
}
