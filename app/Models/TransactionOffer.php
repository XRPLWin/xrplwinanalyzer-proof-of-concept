<?php

namespace App\Models;


class TransactionOffer extends Transaction
{
  protected $table = 'tx_offers';

  /**
   * The attributes that should be cast.
   *
   * @var array
   */
  protected $casts = [
      'time_at' => 'datetime',
      //'is_issuing' => 'boolean'
  ];

}
