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
            $table->double('amount'); //in XRP or currency value
            $table->integer('fee')->unsigned()->default(0); //in drops

            $table->foreignId('issuer_account_id')->nullable();
            //$table->string('issuer',35)->collation('utf8_bin'); //25 to 35 characters, utf8_bin case sensitive
            $table->string('currency')->collation('utf8_bin'); //utf8_bin case sensitive, currency as set in xrpl

            $table->bigInteger('destination_tag')->unsigned()->nullable();
            $table->bigInteger('source_tag')->unsigned()->nullable();

            $table->boolean('is_issuing')->default(false);

            $table->dateTimeTz('time_at', 0);


            $table->foreign('source_account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->foreign('destination_account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->foreign('issuer_account_id')->references('id')->on('accounts')->onDelete('restrict');
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
