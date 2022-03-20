<?php

namespace App\Models;


class TransactionPayment extends Transaction
{
  protected $table = 'tx_payments';

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
      'time_at' => 'datetime',
      'is_issuing' => 'boolean'
  ];

}
