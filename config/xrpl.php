<?php

/**
* @see https://xrpl.org/public-servers.html
*/
return [
  //websocket domain (example: 'xrplcluster.com')
  'server_wss' => 'xrplcluster.com',

  //for connection via php GuzzleHttp, eg http://s1.ripple.com:51234 (reporting server)
  'rippled_server_uri' => 'http://s1.ripple.com:51234',

  //for connection via php GuzzleHttp (full history server)
  //used for method "gateway_balances"
  'rippled_fullhistory_server_uri' => 'https://xrplcluster.com',

  //https://xrpl.org/basic-data-types.html#specifying-time
  'ripple_epoch' => 946684800,

  # TESTNET
  //'rippled_server_uri' => 'https://s.altnet.rippletest.net:51234',
  //'rippled_fullhistory_server_uri' => 'https://s.altnet.rippletest.net:51234'

  'token_source' => 'https://api.xrpldata.com/api/v1/tokens',

  //min ledger index in existance
  'genesis_ledger' => 32570
];
