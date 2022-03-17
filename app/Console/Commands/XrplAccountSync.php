<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Statics\XRPL;
use App\Statics\Account as StaticAccount;
use App\Models\Account;
use App\Models\TransactionPayment;
use App\Models\TransactionTrustset;
use App\Models\TransactionAccountset;


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
      $is_history_synced = false;
      $do = true;
      while($do) {
        $txs = XRPL::account_tx($address,-1,-1,$marker);
        if(isset($txs['result']['status']) && $txs['result']['status'] == 'success')
        {
          foreach($txs['result']['transactions'] as $tx)
          {
            $this->processTransaction($account,$tx['tx']);
            $this->info($txs['result']['ledger_index_max'].' - '.$tx['tx']['ledger_index'].' ('.count($txs['result']['transactions']).')');
            $account->ledger_first_index = $tx['tx']['ledger_index'];
          }
        }
        else
        {
          //something failed
          $is_history_synced = false;
          $do = false;
        }

        if(!isset($txs['result']['marker']))
        {
          //end reached
          $is_history_synced = true;
          $do = false;
        }
        else
          $marker = $txs['result']['marker'];
      }

      $account->is_history_synced = $is_history_synced;
      $account->save();


      if($account->is_history_synced)
      {
        //handle event after full history pull for account
        $analyzed = $this->analyzeSyncedData($account);
      }

      return 0;
    }

    /**
    * After we fully synced account, analyze it and store additional info to db.
    **/
    private function analyzeSyncedData(Account $account) : bool
    {
      if(!$account->is_history_synced)
        return false; //not synced fully

      # 1. Detect hot wallets
      #    To detect hot wallets we will examine transactions and detect large amount of token flow from issuer account.

      return true;

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
      $destination_tag = isset($tx['DestinationTag']) ? $tx['DestinationTag']:null;
      $source_tag = isset($tx['SourceTag']) ? $tx['SourceTag']:null;
      //$this->info($tx['DestinationTag']);
      //dd($tx);
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
      $TransactionPayment->destination_tag = $destination_tag;
      $TransactionPayment->source_tag = $source_tag;
      $TransactionPayment->fee = $tx['Fee']; //in drops
      if(is_array($tx['Amount']))
      {
        //it is payment in currency
        $TransactionPayment->amount = $tx['Amount']['value'];
        $TransactionPayment->issuer_account_id = StaticAccount::GetOrCreate($tx['Account'],$this->current_ledger)->id;
        $TransactionPayment->currency = $tx['Amount']['currency'];
      }
      else
      {
        //it is payment in XRP
        $TransactionPayment->amount = drops_to_xrp($tx['Amount']);
        $TransactionPayment->issuer_account_id = null;
        $TransactionPayment->currency = '';
      }

      $TransactionPayment->save();
      $this->info($tx['Destination'].' '.$tx['Account']);
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
      $txhash = $tx['hash'];

      $TransactionTrustsetCheck = TransactionTrustset::where('txhash',$txhash)->count();
      if($TransactionTrustsetCheck)
        return null; //nothing to do, already stored

      $TransactionTrustset = new TransactionTrustset;
      $TransactionTrustset->txhash = $txhash;

      if($account->account == $tx['Account'])
        $TransactionTrustset->source_account_id = $account->id; //reuse it
      else
        $TransactionTrustset->source_account_id = StaticAccount::GetOrCreate($tx['Account'],$this->current_ledger)->id;

      if($account->account == 'rhSTjeiRC6Zu5mmG4Bmy21uPcwJLhnmQov' ||
      StaticAccount::GetOrCreate($tx['Account'],$this->current_ledger)->account == 'rhSTjeiRC6Zu5mmG4Bmy21uPcwJLhnmQov')
        $this->info(json_encode($tx));
      $TransactionTrustset->fee = $tx['Fee']; //in drops

      if($tx['LimitAmount']['value'] == 0)
        $TransactionTrustset->state = 0; //deleted
      else
        $TransactionTrustset->state = 1; //created

      $TransactionTrustset->issuer_account_id = StaticAccount::GetOrCreate($tx['LimitAmount']['issuer'],$this->current_ledger)->id;
      $TransactionTrustset->currency = $tx['LimitAmount']['currency'];
      $TransactionTrustset->amount = $tx['LimitAmount']['value'];

      $TransactionTrustset->save();

      return null;
    }

    private function processTransaction_AccountSet(Account $account, array $tx)
    {
      return null; //not used yet

      $txhash = $tx['hash'];
      $TransactionAccountsetCheck = TransactionAccountset::where('txhash',$txhash)->count();
      if($TransactionAccountsetCheck)
        return null; //nothing to do, already stored

      $TransactionAccountset = new TransactionAccountset;
      $TransactionAccountset->txhash = $txhash;

      if($account->account == $tx['Account'])
        $TransactionAccountset->source_account_id = $account->id; //reuse it
      else
        $TransactionAccountset->source_account_id = StaticAccount::GetOrCreate($tx['Account'],$this->current_ledger)->id;

      $TransactionAccountset->fee = $tx['Fee']; //in drops

      //$TransactionAccountset->set_flag = $tx['SetFlag'];

      $TransactionAccountset->save();



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
