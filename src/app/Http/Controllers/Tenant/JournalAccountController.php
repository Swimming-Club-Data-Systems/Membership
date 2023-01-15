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
    public function index() {

    }

    public function new(LedgerAccount $ledger) {
        return Inertia::render('Payments/Ledgers/Journals/New', [
        ]);
    }

    public function create(LedgerAccount $ledger, Request $request)
    {
        $request->validate([
            'name' => [
                'required',
                'max:100',
                Rule::unique('ledger_accounts')->where(function ($query) {
                    return $query->where('Tenant', tenant('ID'));
                }),
            ],
            'type' => [
                'required',
                Rule::in(['asset', 'liability', 'equity', 'income', 'expense']),
            ]
        ]);

        $journalAccount = new JournalAccount();
        $journalAccount->name = $request->input('name');
        $journalAccount->ledger = $ledger->ledger;
        $journalAccount->save();

        return redirect(route('payments.ledgers.journals.show', $journalAccount->id));
    }
}
