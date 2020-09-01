<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFacitiliesPropertiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('facitilies_properties', function (Blueprint $table) {
            $table->unsignedBigInteger('propertyId');
            $table->foreign('propertyId')->references('id')->on('properties');
            $table->unsignedBigInteger('facilityId');
            $table->foreign('facilityId')->references('id')->on('facitilies');
            $table->primary(['propertyId', 'facilityId']);
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
        Schema::dropIfExists('facitilies_properties');
    }
}
