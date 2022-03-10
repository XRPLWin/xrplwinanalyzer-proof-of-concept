<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Validation\ValidationException;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

/**
 DELETE THIS
 */
class XRPLController extends Controller
{
  public function account_info($account) : array
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
  public function account_tx(string $account, $marker = null) : array
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


  /**
  * Executes gateway_balances command against XRPL.
  * @return array
  */
  public function account_lines(string $account, $marker = null, $iteration = 1) : array
  {
    $client = new \GuzzleHttp\Client();
    $body = [
      'method' => 'account_lines',
      //'id' => 'xrpl.win_1',
      'params' => [
        [
          'account' => $account,
          'limit' => 400,
          //'marker'
          //"ledger_index" =>  "validated",
          //'hotwallet' => ['rKm4uWpg9tfwbVSeATv4KxDe6mpE9yPkgJ','ra7JkEzrgeKHdzKgo4EUUVBnxggY4z37kt'],
          //'ledger_hash' => '5DB01B7FFED6B67E6B0414DED11E051D2EE2B7619CE0EAA6286D67A3A4D5BDB3',
          //'strict' =>  false,
        ]
      ]
    ];

    if($marker)
    {
      $body['params'][0]['marker'] = $marker;
    }
    //dd(json_encode( $body ));
    $response = $client->request('POST', config('xrpl.rippled_server_uri'), [
      'body' => json_encode( $body ),
      'headers' => [
        //'Accept' => 'application/json',
        'Content-Type' => 'application/json',
      ],
    ]);
    $ret = \json_decode($response->getBody(),true);
    //dd($ret);
    if(isset($ret['result']['marker']) && $iteration <= 4)
    {
      //there are more than limit trustlines...
      $loadmore = $this->account_lines($account,$ret['result']['marker'],($iteration+1));
      if(isset($loadmore['result']['status']) && $loadmore['result']['status'] == 'success')
      {
        //dd($loadmore);
        $loadmore['result']['lines'] = array_merge($ret['result']['lines'],$loadmore['result']['lines']);
        //if($iteration == 3) dd($loadmore,$marker,$lastmarker);
        $ret = $loadmore;
      }
    }

    return $ret;
  }

  /**
  * Executes gateway_balances command agains XRPL.
  * @return array
  */
  public function gateway_balances(string $account) : array
  {
    $client = new \GuzzleHttp\Client();
    $body = [
      'method' => 'gateway_balances',
      //'id' => 'xrpl.win_1',
      'params' => [
        [
          'account' => $account,
          'ledger_index' =>  'validated',
          //'hotwallet' => ['rKm4uWpg9tfwbVSeATv4KxDe6mpE9yPkgJ','ra7JkEzrgeKHdzKgo4EUUVBnxggY4z37kt'],
          //'ledger_hash' => '5DB01B7FFED6B67E6B0414DED11E051D2EE2B7619CE0EAA6286D67A3A4D5BDB3',
          'strict' =>  false,
        ]
      ]
    ];
    //dd(json_encode( $body ));
    $response = $client->request('POST', config('xrpl.rippled_fullhistory_server_uri'), [
      'body' => json_encode( $body ),
      'headers' => [
        //'Accept' => 'application/json',
        'Content-Type' => 'application/json',
      ],
    ]);
    $ret = \json_decode($response->getBody(),true);
  //  dd($ret);
    return $ret;
  }

}
