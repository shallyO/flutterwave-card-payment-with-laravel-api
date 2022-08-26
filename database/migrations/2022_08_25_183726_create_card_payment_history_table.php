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
        Schema::create('card_payment_history', function (Blueprint $table) {
            $table->id();
            $table->string('card_number');
            $table->string('flw_ref')->unique()->nullable();
            $table->string('flw_id')->unique()->nullable();
            $table->string('system_ref')->unique()->nullable();
            $table->string('status');
            $table->unsignedBigInteger('amount');
            $table->bigInteger('customer_id');
            $table->foreign('customer_id')->references('id')->on('customers');
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
        Schema::dropIfExists('card_payment_history');
    }
};
