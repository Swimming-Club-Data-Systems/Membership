<?php

namespace App\Jobs;

use App\Business\Helpers\PhoneNumber;
use App\Exceptions\Accounting\JournalAlreadyExists;
use App\Models\Central\Tenant;
use App\Models\Tenant\Sms;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
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

        if (!$tenant->journal) {
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

        $from = $tenant->alphanumeric_sender_id ? $tenant->alphanumeric_sender_id : "SWIM CLUB";
        if (config('twilio.from') == '+15005550006') {
            // Must be in test mode, overwrite the from number with this
            $from = config('twilio.from');
        }

        foreach ($numbers as $number => $userId) {
            try {
                $response = $client->messages->create(
                    $number,
                    [
                        'from' => $from,
                        'body' => $this->sms->message,
                        'shortenUrls' => true,
                        'maxPrice' => 0.0100
                    ]
                );

                PhoneNumber::create($number)->getDescription();

                $tenant->journal->debit(5 * $response->numSegments, 'SMS message of ' . $response->numSegments . ' ' . Str::plural('segment', $response->numSegments) . ' to ' . Str::mask($number, '*', -6) . ' (' . PhoneNumber::create($number)->getDescription() . ')');
            } catch (TwilioException $e) {
                report($e);
            } catch (\Exception $e) {
                report($e);
            }
        }

        $this->sms->processed = true;
        $this->sms->save();

    }
}
