<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Statics\XRPL;
use App\Models\Account;
use App\Models\TransactionPayment;


class XrplAccountSync extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'xrpl:accountsync {address}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Do a full sync of account';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $address = $this->argument('address');
      $current_ledger = XRPL::ledger_current();


      //validate $account format
      $account = Account::select([
          'id',
          'account',
          'ledger_first_index',
          'ledger_last_index',
        ])
        ->where('account',$address)
        ->first();

      if(!$account)
      {
        $account = new Account;
        $account->account = $address;
        $account->ledger_first_index = $current_ledger;
      }

      $account->ledger_last_index = $current_ledger;

      //TODO adjust ledger indexes and put to account_tx

      $account->save();
      $marker = null;
      $do = true;
      while($do) {
        $txs = XRPL::account_tx($address,-1,-1,$marker);
        if(isset($txs['result']['status']) && $txs['result']['status'] == 'success')
        {
          foreach($txs['result']['transactions'] as $tx)
          {
            $this->processTransaction($tx['tx']);
            $this->info($txs['result']['ledger_index_max'].' - '.$tx['tx']['ledger_index'].' ('.count($txs['result']['transactions']).')');
          }
        }
        else
          $do = false;

        if(!isset($txs['result']['marker']))
          $do = false;
        else
          $marker = $txs['result']['marker'];
      }

      return 0;
    }

    private function processTransaction(array $tx)
    {
      $type = $tx['TransactionType'];
      $method = 'processTransaction_'.$type;
      return $this->{$method}($tx);
    }

    /**
    * Payment to or from in any currency.
    */
    private function processTransaction_Payment(array $tx)
    {
      dd($tx);
      return null;
    }


    private function processTransaction_OfferCreate(array $tx)
    {
      return null;
    }

    private function processTransaction_OfferCancel(array $tx)
    {
      return null;
    }

    private function processTransaction_TrustSet(array $tx)
    {
      return null;
    }

    private function processTransaction_AccountSet(array $tx)
    {
      return null;
    }

    private function processTransaction_AccountDelete(array $tx)
    {
      return null;
    }

    private function processTransaction_SetRegularKey(array $tx)
    {
      return null;
    }

    private function processTransaction_SignerListSet(array $tx)
    {
      return null;
    }

    private function processTransaction_EscrowCreate(array $tx)
    {
      return null;
    }

    private function processTransaction_EscrowFinish(array $tx)
    {
      return null;
    }

    private function processTransaction_EscrowCancel(array $tx)
    {
      return null;
    }

    private function processTransaction_PaymentChannelCreate(array $tx)
    {
      return null;
    }

    private function processTransaction_PaymentChannelFund(array $tx)
    {
      return null;
    }

    private function processTransaction_PaymentChannelClaim(array $tx)
    {
      return null;
    }

    private function processTransaction_DepositPreauth(array $tx)
    {
      return null;
    }
}
