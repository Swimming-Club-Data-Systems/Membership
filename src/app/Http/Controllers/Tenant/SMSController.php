<?php

namespace App\Http\Controllers\Tenant;

use App\Business\Helpers\Money;
use App\Exceptions\Accounting\JournalAlreadyExists;
use App\Http\Controllers\Controller;
use App\Jobs\ProcessSMS;
use App\Models\Central\Tenant;
use App\Models\Tenant\Member;
use App\Models\Tenant\NotifyHistory;
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
    public function index(Request $request)
    {
        $this->authorize('view', Sms::class);

        $sms = null;
        if ($request->query('query')) {
            $sms = Sms::search($request->query('query'))->where('Tenant', tenant('ID'))->query(fn($query) => $query->with(['author']))->paginate(config('app.per_page'));
        } else {
            $sms = Sms::with(['author'])->orderBy('created_at', 'desc')->paginate(config('app.per_page'));
        }

        $sms->getCollection()->transform(function ($item) {
            return [
                'id' => $item->id,
                'author' => [
                    'Forename' => $item->author->Forename,
                    'Surname' => $item->author->Surname,
                ],
                'message' => $item->message,
                'created_at' => $item->created_at,
                'updated_at' => $item->updated_at,
            ];
        });

        return Inertia::render('Notify/SMSHistory', [
            'messages' => $sms->onEachSide(3),
        ]);
    }

    public function new(): \Inertia\Response
    {
        $this->authorize('create', Sms::class);

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
        $this->authorize('create', Sms::class);

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

        // Send a copy to the current user too
        $users[$user->UserID] = $user->Mobile;

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
