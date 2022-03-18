<?php

namespace App\Models;


class TransactionTrustset extends Transaction
{
  protected $table = 'tx_trustsets';

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
      'time_at' => 'datetime',
  ];
  
}
