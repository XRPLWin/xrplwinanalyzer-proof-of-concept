<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTopaccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('topaccounts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('account_id');
            $table->foreignId('issuer_id');
            $table->string('currency')->collation('utf8_bin');
            $table->double('amount'); //currency value
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('restrict');
            $table->foreign('issuer_id')->references('id')->on('accounts')->onDelete('restrict');

        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('topaccounts');
    }
}
