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
        Schema::create('winandplaces', function (Blueprint $table) {
            $table->id();
            $table->decimal('win_odd')->nullable();
            $table->decimal('place_odd')->nullable();
            $table->integer('event_id');
            $table->integer('event_no');
            $table->string('draw');
            $table->string('name')->nullable();
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
        Schema::dropIfExists('winandplaces');
    }
};
