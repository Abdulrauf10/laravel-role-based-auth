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
        Schema::create('test_participants', function (Blueprint $table) {
            $table->uuid('id'); 
            $table->char('code',6); // this id shoud be 6 digit char
            $table->foreignUuid('test_id');
            $table->foreignId('participant_id');
            $table->unsignedBigInteger('assesor_id')->nullable();
            $table->unsignedBigInteger('verbatimer_id')->nullable();
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
        Schema::dropIfExists('test_participants');
    }
};
