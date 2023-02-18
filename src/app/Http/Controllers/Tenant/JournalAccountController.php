<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\JournalAccount;
use App\Models\Tenant\LedgerAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class JournalAccountController extends Controller
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

    public function index() {

    }

    public function new(LedgerAccount $ledger) {
        return Inertia::render('Payments/Ledgers/Journals/New', [
            'ledger_id' => $ledger->id,
            'ledger_name' => $ledger->name,
            'ledger_is_system' => $ledger->is_system,
        ]);
    }

    public function create(LedgerAccount $ledger, Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'max:100',
                Rule::unique('journal_accounts')->where(function ($query) {
                    return $query->where('Tenant', tenant('ID'));
                }),
            ],
            'currency' => [
                'required',
                Rule::in(['GBP']),
            ]
        ]);

        $journalAccount = new JournalAccount();
        $journalAccount->name = $request->input('name');
        $journalAccount->ledgerAccount()->associate($ledger);
        $journalAccount->save();

        return redirect(route('payments.ledgers.journals.show', [$ledger->id, $journalAccount->id]));
    }

    public function show(LedgerAccount $ledger, JournalAccount $journal)
    {
        return Inertia::render('Payments/Ledgers/Journals/Show', [
            'id' => $journal->id,
            'ledger_id' => $ledger->id,
            'ledger_name' => $ledger->name,
            'name' => $journal->name,
            'is_system' => $journal->is_system,
        ]);
    }
}
