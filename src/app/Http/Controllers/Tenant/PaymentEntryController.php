<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\ManualPaymentEntry;
use App\Models\Tenant\User;
use Illuminate\Http\Request;
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
        $this->authorize('amend', $entry);

        return Inertia::render('Payments/Entry', [
            'id' => $entry->id,
        ]);
    }

    public function create()
    {

    }
}
