<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePvDataTable extends Migration
{
    public function up()
    {
        Schema::create('pv_data', function (Blueprint $table) {
            $table->id();
            $table->float('Vdc');
            $table->float('Idc');
            $table->float('Power');
            $table->float('Energydc');
            $table->float('Vac');
            $table->float('Iac');
            $table->float('Power_ac');
            $table->float('Frequency');
            $table->float('Power_factor');
            $table->float('Energy_ac');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pv_data');
    }
};
