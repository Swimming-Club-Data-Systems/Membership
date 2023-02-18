<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\LedgerAccount;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Inertia\Inertia;

class LedgerAccountController extends Controller
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

    public function index()
    {
        $ledgers = LedgerAccount::orderBy('name', 'asc')->paginate(config('app.per_page'));

        return Inertia::render('Payments/Ledgers/Index', [
            'ledgers' => $ledgers->onEachSide(3),
        ]);
    }

    public function new()
    {
        return Inertia::render('Payments/Ledgers/New', [
        ]);
    }

    public function create(Request $request)
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

        $ledgerAccount = new LedgerAccount();
        $ledgerAccount->name = $request->input('name');
        $ledgerAccount->type = $request->input('type');
        $ledgerAccount->save();

        return redirect(route('payments.ledgers.show', $ledgerAccount->id));
    }

    public function show(LedgerAccount $ledger, Request $request)
    {
        return Inertia::render('Payments/Ledgers/Show', [
            'journals' => $ledger->journalAccounts()->paginate()->onEachSide(3),
            'id' => $ledger->id,
            'name' => $ledger->name,
            'type' => $ledger->type,
            'is_system' => $ledger->is_system,
        ]);
    }
}
