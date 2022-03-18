<?php

namespace App\Models;


class TransactionAccountset extends Transaction
{
  protected $table = 'tx_accountsets';

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
      'time_at' => 'datetime',
  ];

}
