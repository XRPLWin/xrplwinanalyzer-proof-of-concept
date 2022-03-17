<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTxAccountsetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('tx_accountsets', function (Blueprint $table) {
          $table->id();
          $table->char('txhash',64)->collation('utf8_bin');
          $table->foreignId('source_account_id');

          //$table->integer('set_flag')->unsigned();

          $table->integer('fee')->unsigned()->default(0); //in drops

          $table->foreign('source_account_id')->references('id')->on('accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('tx_accountsets');
    }
}
