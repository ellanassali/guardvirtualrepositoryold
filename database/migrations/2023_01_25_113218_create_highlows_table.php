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
        Schema::create('highlows', function (Blueprint $table) {
            $table->id();
            $table->decimal('h_odd')->nullable();
            $table->string('l_odd')->nullable();
            $table->integer('event_id');
            $table->integer('event_no');
            $table->dateTime('event_time');
            $table->dateTime('finish_time');
            $table->string('event_type');
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
        Schema::dropIfExists('highlows');
    }
};
