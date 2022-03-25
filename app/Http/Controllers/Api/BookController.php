<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Statics\XRPL;
use App\Statics\Account as StaticAccount;
use App\Models\Account;
use App\Loaders\AccountLoader;

class BookController extends Controller
{

  private function fetch_offers(array $params)
  {
    $orderbookResponse = XRPL::book_offers($params);

    if(isset($orderbookResponse['result']['status']) && $orderbookResponse['result']['status'] == 'success')
    {
      $offers = $orderbookResponse['result']['offers'];
      if(!is_array($offers))
        return [];

      if(count($offers) == 0)
        return [];

      return $offers;
    }
    return [];
  }

  /**
  * @param $amount - amount from tl.
  https://github.com/XRPL-Labs/net-worth-xapp/blob/main/src/plugins/xapp-vue.js
  https://github.com/XRPL-Labs/XRPL-Orderbook-Reader/blob/0378825be82cb21402a9a719c79bfc12a88e2f31/src/index.ts
  https://github.com/XRPL-Labs/XRPL-Orderbook-Reader/blob/0378825be82cb21402a9a719c79bfc12a88e2f31/src/parser/LiquidityParser.ts#L54
  http://xlanalyzer.test/book/liquidity_check/rGQrZvndQsJV2S5cnSdiRFMPT1Fz1Ccvuj/416E696D61436F696E0000000000000000000000/1500

  */
  public function liquidity_check(string $issuer, string $currency, $amount)
  {
    $r = [ 'price' => 0 ];

    $orderbook = $this->fetch_offers([
      'taker_gets' => [ 'currency' => 'XRP' ],
      'taker_pays' => [ 'issuer' => $issuer, 'currency' => $currency],
      'limit' => 10 //500
    ]);

    $test = $this->LiquidityParser($orderbook);

    /*$orderbookReverse = $this->fetch_offers([
      'taker_pays' => [ 'currency' => 'XRP' ],
      'taker_gets' => [ 'issuer' => $issuer, 'currency' => $currency],
      'limit' => 10 //500
    ]);*/

    $finalBookLine = collect($orderbook)->where('_Capped' != null)->last();
    //$rate =
    dd($orderbook,$orderbookReverse);

    return response()->json($r);
  }

  /**
  * @return [  _ExchangeRate: number
    _I_Spend_Capped: number
    _I_Get_Capped: number
    _CumulativeRate: number
    _CumulativeRate_Cap: number
    _Capped: boolean
    ]
  */
  private function LiquidityParser(array $offers)
  {

    foreach($offers as $offer)
    {
      $_PaysEffective = isset($offer['taker_gets_funded']) ? $offer['taker_gets_funded'] : $offer['TakerGets'];
      $_GetsEffective = isset($offer['taker_pays_funded']) ? $offer['taker_pays_funded'] : $offer['TakerPays'];
      dd($_PaysEffective,$_GetsEffective);
    }
  }

}
