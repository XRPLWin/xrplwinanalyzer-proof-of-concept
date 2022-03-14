<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Statics\XRPL;
use App\Statics\Account as StaticAccount;
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

    private $current_ledger;
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $address = $this->argument('address');
      $this->current_ledger = XRPL::ledger_current();


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
        $account = StaticAccount::GetOrCreate($address,$this->current_ledger);
      }

      $account->ledger_last_index = $this->current_ledger;

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
            $this->processTransaction($account,$tx['tx']);
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

    private function processTransaction(Account $account, array $tx)
    {
      $type = $tx['TransactionType'];
      $method = 'processTransaction_'.$type;
      return $this->{$method}($account, $tx);
    }

    /**
    * Payment to or from in any currency.
    */
    private function processTransaction_Payment(Account $account, array $tx)
    {
      $txhash = $tx['hash'];
      // Check existing tx
      $TransactionPaymentCheck = TransactionPayment::where('txhash',$txhash)->count();
      if($TransactionPaymentCheck)
        return null; //nothing to do, already stored

      if($account->account == $tx['Account'])
      {
        $source_account = $account;
        $destination_account = StaticAccount::GetOrCreate($tx['Destination'],$this->current_ledger);
      }
      else
      {
        $source_account = StaticAccount::GetOrCreate($tx['Account'],$this->current_ledger);
        $destination_account = $account;
      }


      $TransactionPayment = new TransactionPayment;
      $TransactionPayment->txhash = $txhash;
      $TransactionPayment->source_account_id = $source_account->id;
      $TransactionPayment->destination_account_id = $destination_account->id;
      $TransactionPayment->save();

      $this->info($tx['Destination'].' '.$tx['Account']);

      //if($destination == 'raTZKmBYyPQdMXsRcne95UMoUQKBvjLXPv')
      //  dd($tx);
      $this->info($tx['Destination']);
    //  dd($tx);
      return null;
    }


    private function processTransaction_OfferCreate(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_OfferCancel(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_TrustSet(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_AccountSet(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_AccountDelete(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_SetRegularKey(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_SignerListSet(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_EscrowCreate(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_EscrowFinish(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_EscrowCancel(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_PaymentChannelCreate(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_PaymentChannelFund(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_PaymentChannelClaim(Account $account, array $tx)
    {
      return null;
    }

    private function processTransaction_DepositPreauth(Account $account, array $tx)
    {
      return null;
    }
}
