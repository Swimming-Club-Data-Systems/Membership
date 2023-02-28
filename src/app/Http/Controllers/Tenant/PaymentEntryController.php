<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Http\Requests\Tenant\ManualPaymentEntryUserPostRequest;
use App\Models\Tenant\ManualPaymentEntry;
use App\Models\Tenant\ManualPaymentEntryLine;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Redirect;
use Inertia\Inertia;

class PaymentEntryController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
    }

    public function new(Request $request)
    {
        $this->authorize('create', ManualPaymentEntry::class);

        /** @var User $user */
        $user = $request->user();

        // Create a ManualPaymentEntry record to update in real time
        // Uncomplete manual payment entries are prunable

        $entry = new ManualPaymentEntry();
        $entry->user()->associate($user);
        $entry->save();

        return redirect()->route('payments.entries.amend', $entry);
    }

    public function amend(ManualPaymentEntry $entry)
    {
        $this->authoriseAmendment($entry);

        $users = $entry->users()->orderBy('created_at')->get();
        $users->transform(function (User $user) use ($entry) {
            return [
                'manual_payment_entry_id' => $entry->id,
                'user_id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
            ];
        });

        return Inertia::render('Payments/Entry', [
            'id' => $entry->id,
            'users' => $users,
            'lines' => $entry->lines()->get(),
        ]);
    }

    private function authoriseAmendment($entry)
    {
        $this->authorize('amend', $entry);

        if ($entry->posted) {
            abort(400, 'The Manual Payment Entry you are trying to amend has already been posted.');
        }
    }

    public function post(ManualPaymentEntry $entry)
    {
        $this->authoriseAmendment($entry);
    }

    public function addUser(ManualPaymentEntry $entry, ManualPaymentEntryUserPostRequest $request)
    {
        $this->authoriseAmendment($entry);

        /** @var User $user */
        $user = User::findOrFail($request->integer('user_select'));

        $entry->users()->attach($user);

        $request->session()->flash('flash_bag.manage_users.success', "{$user->name} has been added.");

        return Redirect::route('payments.entries.amend', $entry);
    }

    public function deleteUser(ManualPaymentEntry $entry, User $user, Request $request)
    {
        $this->authoriseAmendment($entry);

        $entry->users()->detach($user);

        $request->session()->flash('flash_bag.manage_users.success', "{$user->name} has been removed.");

        return Redirect::route('payments.entries.amend', $entry);
    }

    public function addLine(ManualPaymentEntry $entry)
    {
        $this->authoriseAmendment($entry);
    }

    public function deleteLine(ManualPaymentEntry $entry, ManualPaymentEntryLine $line)
    {
        $this->authoriseAmendment($entry);
    }
}
