<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAggregateDailyPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('aggregate_daily_payments', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id');
            $table->double('amount')->default(0); //in XRP or currency value
            $table->double('balance')->default(0); //in XRP or currency value total balance on this day
            $table->date('date');
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('aggregate_daily_payments');
    }
}
