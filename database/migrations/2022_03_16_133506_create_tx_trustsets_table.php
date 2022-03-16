<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTxTrustsetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tx_trustsets', function (Blueprint $table) {
            $table->id();
            $table->char('txhash',64)->collation('utf8_bin');
            $table->foreignId('source_account_id');
            $table->tinyInteger('state')->default(0); //0 delete, 1 created or modified
            $table->integer('fee')->unsigned()->default(0); //in drops

            $table->foreignId('issuer_account_id')->nullable();
            $table->string('currency')->collation('utf8_bin'); //utf8_bin case sensitive, currency as set in xrpl

            $table->decimal('amount',30,15); //in XRP or currency value

            $table->foreign('source_account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->foreign('issuer_account_id')->references('id')->on('accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_trustsets');
    }
}
