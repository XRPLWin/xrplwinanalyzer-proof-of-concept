<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTxPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tx_payments', function (Blueprint $table) {
            $table->id();
            $table->char('txhash',64)->collation('utf8_bin');
            $table->foreignId('source_account_id');
            $table->foreignId('destination_account_id');

            $table->foreign('source_account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->foreign('destination_account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->index('txhash'); //hash or b-tree (default)? TODO benchmark both.
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_payments');
    }
}
