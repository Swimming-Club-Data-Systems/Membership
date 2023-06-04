<?php

namespace App\Jobs;

use App\Business\Helpers\Money;
use App\Business\Helpers\PhoneNumber;
use App\Enums\Queue;
use App\Exceptions\Accounting\JournalAlreadyExists;
use App\Mail\SmsSent;
use App\Models\Central\Tenant;
use App\Models\Tenant\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Twilio\Exceptions\TwilioException;
use Twilio\Rest\Client;

class ProcessSMS implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public Sms $sms;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct(Sms $sms)
    {
        $this->sms = $sms->withoutRelations();
        $this->onQueue(Queue::NOTIFY->value);
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        /** @var Tenant $tenant */
        $tenant = tenant();

        if (! $tenant->journal) {
            try {
                $tenant->initJournal();
                $tenant = $tenant->fresh();
            } catch (JournalAlreadyExists $e) {
                // Ignore, we already checked existence
            }
        }

        $numbers = [];
        foreach ($this->sms->recipients()->get() as $recipient) {
            if ($recipient->MobileComms) {
                $numbers[$recipient->Mobile] = $recipient->UserID;
            }
        }

        $client = new Client(config('twilio.sid'), config('twilio.token'), null, config('twilio.region'));
        $client->setEdge(config('twilio.edge'));

        $from = $tenant->alphanumeric_sender_id ? $tenant->alphanumeric_sender_id : 'SWIM CLUB';
        if (config('twilio.from') == '+15005550006') {
            // Must be in test mode, overwrite the from number with this
            $from = config('twilio.from');
        }

        $totalFee = 0;
        $sentUsers = 0;
        $failedUsers = 0;

        foreach ($numbers as $number => $userId) {
            try {
                // Check balance > 0
                if ($tenant->journal->getBalance()->getAmount() < 0) {
                    // The tenant is out of funds and needs to top up
                    break;
                }

                $response = $client->messages->create(
                    $number,
                    [
                        'from' => $from,
                        'body' => $this->sms->message,
                        'shortenUrls' => true,
                        'maxPrice' => 0.0100,
                    ]
                );

                PhoneNumber::create($number)->getDescription();

                $fee = 5 * $response->numSegments;
                $totalFee += $fee;

                // Debit the tenant and reference the Sms model
                $transaction = $tenant->journal->debit($fee, 'SMS message of '.$response->numSegments.' '.Str::plural('segment', $response->numSegments).' to '.Str::mask($number, '*', -6).' ('.PhoneNumber::create($number)->getDescription().')');
                $transaction->referencesObject($this->sms);
                $sentUsers++;
            } catch (TwilioException $e) {
                report($e);
                $failedUsers++;
            } catch (\Exception $e) {
                report($e);
                $failedUsers++;
            }
        }

        $this->sms->processed = true;
        $this->sms->save();

        Mail::to($this->sms->author)->send(new SmsSent($this->sms, Money::formatCurrency($totalFee), $sentUsers, $failedUsers));

    }
}
