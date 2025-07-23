<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTaxnamesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taxnames', function (Blueprint $table) {
            $table->increments('id');
            $table->tinyInteger('type')->comment('1 = percentage, 0 = fixed amount');
            $table->double('value');
            $table->string('name_ar', 100);
            $table->string('name_en', 100);
            $table->tinyInteger('optional')->comment("1=>Required 0=>NonRequired");
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
        Schema::dropIfExists('taxnames');
    }
}
