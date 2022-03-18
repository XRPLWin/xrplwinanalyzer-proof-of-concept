<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
#use Illuminate\Support\Facades\Artisan;
use App\Statics\XRPL;
use App\Statics\Account as StaticAccount;
use App\Models\Account;
use App\Models\Activation;
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
    protected $signature = 'xrpl:accountsync
                            {address : XRP account address}
                            {--recursiveaccountqueue : Enable to create additional queues for other accounts}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Do a full sync of account';

    protected $recursiveaccountqueue = false;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    private $ledger_current = -1;
    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
      $address = $this->argument('address');
      $this->recursiveaccountqueue = $this->option('recursiveaccountqueue'); //boolean

      $this->ledger_current = XRPL::ledger_current();

      //validate $account format
      $account = Account::select([
          'id',
          'account',
          'ledger_first_index',
          'ledger_last_index',
          'is_history_synced'
        ])
        ->where('account',$address)
        ->first();

      if(!$account)
      {
        $account = StaticAccount::GetOrCreate($address,$this->ledger_current);
      }

      //dd($account );


      //$account->ledger_last_index = 40407578; //$this->ledger_current
      //$account->ledger_last_index = 44317866; //$this->ledger_current

      $ledger_index_max = $this->ledger_current; //from current ledger

      # Adjust ledger history index limit.
      if($account->is_history_synced) {
        //pull only new (we have already older data synced)
        $ledger_index_min = $account->ledger_last_index;
      } else {
        //pull full history
        $ledger_index_min = -1; //back to full history
      }

      $account->ledger_last_index = $this->ledger_current;

      $account->save();
      $marker = null;
      $is_history_synced = false;
      $do = true;
      while($do) {
        $txs = XRPL::account_tx($address,$ledger_index_min,$account->ledger_last_index,$marker);
        //dd($txs);
        if(isset($txs['result']['status']) && $txs['result']['status'] == 'success')
        {
          foreach($txs['result']['transactions'] as $tx)
          {
            $this->processTransaction($account,$tx);
            $this->info($txs['result']['ledger_index_max'].' - '.$tx['tx']['ledger_index'].' ('.count($txs['result']['transactions']).')');
            if($account->ledger_first_index > $tx['tx']['ledger_index'])
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
        //$analyzed = $this->analyzeSyncedData($account);
        $analyzed = StaticAccount::analyzeData($account);
      }

      return 0;
    }


    private function processTransaction(Account $account, array $tx)
    {
      $type = $tx['tx']['TransactionType'];
      $method = 'processTransaction_'.$type;
      if($tx['meta']['TransactionResult'] != 'tesSUCCESS')
      {
        //$this->info($tx['meta']['TransactionResult'].': '.\json_encode($tx));
        return null; //do not log failed transactions
      }
      //dd($tx['meta']['TransactionResult']);
      return $this->{$method}($account, $tx['tx'], $tx['meta']);
    }

    /**
    * Payment to or from in any currency.
    */
    private function processTransaction_Payment(Account $account, array $tx, array $meta)
    {
      $txhash = $tx['hash'];

      //if($txhash == '132C28AF6AB9416D111964263CC8AC017325E1FA32A33179D6975581CB45432F') dd($tx,$meta);

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
        $destination_account = StaticAccount::GetOrCreate($tx['Destination'],$this->ledger_current);
      }
      else
      {
        $source_account = StaticAccount::GetOrCreate($tx['Account'],$this->ledger_current);
        $destination_account = $account;
      }

      $TransactionPayment = new TransactionPayment;
      $TransactionPayment->txhash = $txhash;
      $TransactionPayment->source_account_id = $source_account->id;
      $TransactionPayment->destination_account_id = $destination_account->id;
      $TransactionPayment->destination_tag = $destination_tag;
      $TransactionPayment->source_tag = $source_tag;
      $TransactionPayment->fee = $tx['Fee']; //in drops
      $TransactionPayment->time_at = ripple_epoch_to_carbon($tx['date']);
      if(is_array($tx['Amount']))
      {
        //it is payment in currency
        $TransactionPayment->amount = $tx['Amount']['value'];
        $TransactionPayment->issuer_account_id = StaticAccount::GetOrCreate($tx['Account'],$this->ledger_current)->id;
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


      //Check account activation
      if(isset($meta['AffectedNodes'])) {

        foreach($meta['AffectedNodes'] as $AffectedNode)
        {
          if(isset($AffectedNode['CreatedNode']))
          {
            /*
            $AffectedNode['CreatedNode'] = array:3 [
              "LedgerEntryType" => "AccountRoot"
              "LedgerIndex" => "6E705A13D395F1CD1E5E69C88D7F03C67588ECF551E37F64CF7A9EABDEF78606"
              "NewFields" => array:3 [
                "Account" => "r3rdCdrFdKG1YqMgJjXDZqeStGayzxQzWy"
                "Balance" => "26250000"
                "Sequence" => 1
              ]
            ]
            */
            if(isset($AffectedNode['CreatedNode']['LedgerEntryType']) && $AffectedNode['CreatedNode']['LedgerEntryType'] ==  'AccountRoot')
            {

              // save account activation $TransactionPayment->source_account_id created $AffectedNode['CreatedNode'].NewFields.Account ($TransactionPayment->destination_account_id)

              $this->info('Activation: '.$source_account->account.' created '.$AffectedNode['CreatedNode']['NewFields']['Account']);
              $Activation = new Activation;
              $Activation->tx_payment_id = $TransactionPayment->id;
              $Activation->source_account_id = $TransactionPayment->source_account_id;
              $Activation->destination_account_id = $TransactionPayment->destination_account_id;
              $Activation->save();

              //

              if($this->recursiveaccountqueue)
              {
                if($account->account != $source_account->account)
                {
                  //parent is created this account, queue parent
                  $this->info('### Queued account: '.$source_account->account);
                  $source_account->sync(true);
                }
                /*dd($tx,$destination_account,$account->account);
                if($destination_account->account == $AffectedNode['CreatedNode']['NewFields']['Account'])
                {
                  $this->info('### Queued account: '.$destination_account->account);
                  $destination_account->sync(true);
                }
                else
                {
                  dd($AffectedNode['CreatedNode']['NewFields']['Account'], $destination_account);
                }*/

                //queue fullsync of account: $TransactionPayment->destination_account_id
                /*Artisan::queue('xrpl:accountsync', [
                    'address' => $AffectedNode['CreatedNode']['NewFields']['Account'],
                    '--recursiveaccountqueue' => true,
                    '--queue' => 'default'
                ]);*/
                //dd($AffectedNode['CreatedNode']['NewFields']['Account']);
              }

            }
            //dd($TransactionPayment,$account,$AffectedNode['CreatedNode']);
            break;
          }
        }
      }
      //dd($meta['AffectedNodes']);


      //$this->info($tx['Destination'].' '.$tx['Account']);
      return null;
    }


    private function processTransaction_OfferCreate(Account $account, array $tx, array $meta)
    {
      return null;
    }

    private function processTransaction_OfferCancel(Account $account, array $tx, array $meta)
    {
      return null;
    }

    private function processTransaction_TrustSet(Account $account, array $tx, array $meta)
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
        $TransactionTrustset->source_account_id = StaticAccount::GetOrCreate($tx['Account'],$this->ledger_current)->id;

      $TransactionTrustset->fee = $tx['Fee']; //in drops

      if($tx['LimitAmount']['value'] == 0)
        $TransactionTrustset->state = 0; //deleted
      else
        $TransactionTrustset->state = 1; //created

      $TransactionTrustset->issuer_account_id = StaticAccount::GetOrCreate($tx['LimitAmount']['issuer'],$this->ledger_current)->id;
      $TransactionTrustset->currency = $tx['LimitAmount']['currency'];
      $TransactionTrustset->amount = $tx['LimitAmount']['value'];
      $TransactionTrustset->time_at = ripple_epoch_to_carbon($tx['date']);

      $TransactionTrustset->save();

      return null;
    }

    private function processTransaction_AccountSet(Account $account, array $tx, array $meta)
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
        $TransactionAccountset->source_account_id = StaticAccount::GetOrCreate($tx['Account'],$this->ledger_current)->id;

      $TransactionAccountset->fee = $tx['Fee']; //in drops
      $TransactionAccountset->time_at = ripple_epoch_to_carbon($tx['date']);

      //$TransactionAccountset->set_flag = $tx['SetFlag'];

      $TransactionAccountset->save();



      return null;
    }

    private function processTransaction_AccountDelete(Account $account, array $tx, array $meta)
    {
      return null;
    }

    private function processTransaction_SetRegularKey(Account $account, array $tx, array $meta)
    {
      return null;
    }

    private function processTransaction_SignerListSet(Account $account, array $tx, array $meta)
    {
      return null;
    }

    private function processTransaction_EscrowCreate(Account $account, array $tx, array $meta)
    {
      return null;
    }

    private function processTransaction_EscrowFinish(Account $account, array $tx, array $meta)
    {
      return null;
    }

    private function processTransaction_EscrowCancel(Account $account, array $tx, array $meta)
    {
      return null;
    }

    private function processTransaction_PaymentChannelCreate(Account $account, array $tx, array $meta)
    {
      return null;
    }

    private function processTransaction_PaymentChannelFund(Account $account, array $tx, array $meta)
    {
      return null;
    }

    private function processTransaction_PaymentChannelClaim(Account $account, array $tx, array $meta)
    {
      return null;
    }

    private function processTransaction_DepositPreauth(Account $account, array $tx, array $meta)
    {
      return null;
    }
}
