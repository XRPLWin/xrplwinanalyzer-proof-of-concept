<?php

namespace App\Statics;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class XRPL
{
  public static function account_info(string $account) : array
  {
    $client = new \GuzzleHttp\Client();
    $body = [
      'method' => 'account_info',
      'params' => [
          [
            'account' => $account,
            'strict' =>  false,
            'ledger_index' => 'current',
            'queue' =>  false
          ]
        ]
      ];
    $response = $client->request('POST', config('xrpl.rippled_server_uri'), [
      'body' => json_encode( $body ),
      'headers' => [
        //'Accept' => 'application/json',
        'Content-Type' => 'application/json',
      ],
    ]);
    $ret = \json_decode($response->getBody(),true);

    return $ret;
  }


  /**
  * Retrieves account transacitons, from marker or without it.
  * @param string $account - xrp account address
  * @param nullable array $marker - marker is offset
  * @see https://xrpl.org/account_tx.html
  */
  public static function account_tx(string $account, $marker = null) : array
  {
    $client = new \GuzzleHttp\Client();
    $body = [
      //'id' => 'xrpl.win_1',
      'method' => 'account_tx',
      'params' => [
        [
          'account' => $account,
          'ledger_index_min' => -1,
          'ledger_index_max' => -1,
          'binary' => false,
          'forward' => false,
          'limit' => 200,
          //'marker'
        ]
      ]
    ];

    if($marker)
    {
      $body['params'][0]['marker'] = $marker;
    }

    $response = $client->request('POST', config('xrpl.rippled_server_uri'), [
      'body' => json_encode( $body ),
      'headers' => [
        //'Accept' => 'application/json',
        'Content-Type' => 'application/json',
      ],
    ]);
    $ret = \json_decode($response->getBody(),true);
    return $ret;
  }

}
