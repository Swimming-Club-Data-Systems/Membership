<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ManualPaymentEntry;
use App\Models\Tenant\ManualPaymentEntryLine;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
use Inertia\Inertia;
use Inertia\Response;

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
        $this->authorize('create');

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

        return Inertia::render('Payments/Entry', [
            'id' => $entry->id,
        ]);
    }

    public function post(ManualPaymentEntry $entry)
    {
        $this->authoriseAmendment($entry);
    }

    private function authoriseAmendment($entry) {
        $this->authorize('amend', $entry);

        if ($entry->posted) {
            abort(400, 'The Manual Payment Entry you are trying to amend has already been posted.');
        }
    }

    public function addUser(ManualPaymentEntry $entry)
    {
        $this->authoriseAmendment($entry);
    }

    public function deleteUser(ManualPaymentEntry $entry, User $user)
    {
        $this->authoriseAmendment($entry);
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
