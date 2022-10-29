<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;
use Twilio\Rest\Client;

class SMSController extends Controller
{
    public function new(): \Inertia\Response
    {
        abort_unless(config('twilio.sid') && config('twilio.token'), 404);
        return Inertia::render('Notify/SMS', [

        ]);
    }

    public function store(Request $request)
    {
        abort_unless(config('twilio.sid') && config('twilio.token'), 404);

        /** @var Tenant $tenant */
        $tenant = tenant();

        try {
            $client = new Client(config('twilio.sid'), config('twilio.token'));

            $from = $tenant->alphanumeric_sender_id ? $tenant->alphanumeric_sender_id : "SWIM CLUB";

            $client->messages->create(
                '+447577002981',
                [
                    'from' => $from,
                    'body' => $request->input('message'),
                ]
            );

            $request->session()->flash('success', 'We have sent your SMS');
        } catch (\Exception $e) {
            report($e);
            $request->session()->flash('error', $e->getMessage());
        }

        return Redirect::route('notify.sms.new');
    }
}
