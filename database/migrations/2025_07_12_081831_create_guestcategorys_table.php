<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateGuestcategorysTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('guestcategorys', function (Blueprint $table) {
            $table->increments('id');
            $table->string('NameCategoryAr', 50);
            $table->string('NameCategoryEn', 50);
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
        Schema::dropIfExists('guestcategorys');
    }
}
