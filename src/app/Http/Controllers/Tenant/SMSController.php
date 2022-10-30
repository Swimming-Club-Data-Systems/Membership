<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Money;
use App\Exceptions\Accounting\JournalAlreadyExists;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessSMS;
use App\Models\Central\Tenant;
use App\Models\Tenant\Member;
use App\Models\Tenant\Sms;
use App\Models\Tenant\Squad;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Validation\ValidationException;
use Inertia\Inertia;

class SMSController extends Controller
{
    public function new(): \Inertia\Response
    {
        abort_unless(config('twilio.sid') && config('twilio.token'), 404);

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

        $balance = $tenant->journal->getBalance();

        $squads = Squad::orderBy('SquadFee', 'desc')->orderBy('SquadName', 'asc')->get()->map(function (Squad $squad) {
            return [
                'id' => $squad->SquadID,
                'name' => $squad->SquadName,
            ];
        });

        return Inertia::render('Notify/SMS', [
            'squads' => $squads,
            'formatted_balance' => Money::formatCurrency($balance->getAmount(), $balance->getCurrency()),
            'balance' => $balance->getAmount(),
        ]);
    }

    /**
     * @throws ValidationException
     */
    public function store(Request $request)
    {
        abort_unless(config('twilio.sid') && config('twilio.token'), 404);

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

        $balance = $tenant->journal->getBalance();

        // Can not send if 0 balance
        abort_unless($balance->getAmount() > 0, 404);

        $validated = $request->validate([
            'message' => [
                'required',
                'max:160',
            ],
        ]);

        /** @var User $user */
        $user = Auth::user();

        $sms = new Sms;

        $sms->message = trim($request->input('message'));
        $sms->author()->associate($user);

        $users = [];

        $sms->save();

        foreach (Squad::all() as $squad) {
            /** @var Squad $squad */
            if ($request->boolean('squads.' . $squad->SquadID)) {
                $sms->squads()->attach($squad);
                foreach ($squad->members()->with(['user'])->get() as $member) {
                    /** @var Member $member */
                    if ($member->user && $member->user->Mobile && $member->user->MobileComms) {
                        $users[$member->user->UserID] = $member->user->Mobile;
                    }
                }
            }
        }

        if (sizeof($users) == 0) {
            // Throw back to the form
            $sms->delete();
            throw ValidationException::withMessages([
                'users' => 'There are no subscribed users for your selection'
            ]);
        }


        foreach ($users as $userId => $mobile) {
            $sms->recipients()->attach($userId, [], false);
        }

        ProcessSMS::dispatch($sms);

        $request->session()->flash('success', 'We\'re sending your message to ' . sizeof($users) . ' users.');

        return Redirect::route('notify.sms.new');
    }
}
