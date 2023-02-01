<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('reverseforecastresults', function (Blueprint $table) {
            $table->id();
            $table->integer('event_id');
            $table->integer('event_no');
            $table->dateTime('event_time');
            $table->dateTime('event_finishTime');
            $table->string('event_type');
            $table->string('selection_id');
            $table->decimal('odd');
            $table->string('position_one');
            $table->string('position_two');
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
        Schema::dropIfExists('reverseforecastresults');
    }
};
