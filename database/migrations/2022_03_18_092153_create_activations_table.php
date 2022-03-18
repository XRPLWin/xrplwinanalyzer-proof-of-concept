<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateActivationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('activations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('source_account_id');
            $table->foreignId('destination_account_id');
            $table->foreignId('tx_payment_id');

            //$table->decimal('amount',30,15); //in XRP or currency value, used for create activation

            $table->foreign('source_account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->foreign('destination_account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->foreign('tx_payment_id')->references('id')->on('tx_payments')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('activations');
    }
}
