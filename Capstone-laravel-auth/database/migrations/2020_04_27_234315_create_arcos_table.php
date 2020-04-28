<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateArcosTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('arcos', function (Blueprint $table) {
            $table->increments('id');
            $table->timestamps();
            $table->string('Reporting_Registrant_Number');
            $table->string('Transaction_Code');
            $table->string('ActionIndicator');
            $table->string('National_Drug_Code');
            $table->integer('Quantity');
            $table->string('Unit');
            $table->string('Associate_Registrant_Number');
            $table->string('DEA_Number');
            $table->string('Transaction_Date');
            $table->string('Correction_Number');
            $table->string('Strength');
            $table->integer('Transaction_Identifier');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('arcos');
    }
}
