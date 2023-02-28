<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\JournalAccount;
use App\Models\Tenant\LedgerAccount;
use App\Models\Tenant\User;
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

    public function combobox(Request $request)
    {
        $journals = null;
        if ($request->query('query')) {
            $journals = JournalAccount::search($request->query('query'))
                ->where('Tenant', tenant('ID'))
                ->paginate(50);
        }

        $journalsArray = [];

        $selectedJournal = null;
        if ($request->query('id')) {
            /** @var JournalAccount $selectedJournal */
            $selectedJournal = JournalAccount::find($request->query('id'));
            if ($selectedJournal) {
                $journalsArray[] = [
                    'id' => $selectedJournal->id,
                    'name' => $selectedJournal->name,
                ];
            }
        }

        if ($journals) {
            foreach ($journals as $journal) {
                /** @var JournalAccount $journal */
                if ($selectedJournal == null || $selectedJournal->id !== $journal->id) {
                    $journalsArray[] = [
                        'id' => $journal->id,
                        'name' => $journal->name,
                    ];
                }
            }
        }

        $responseData = [
            'data' => $journalsArray,
            'has_more_pages' => $journals && $journals->hasMorePages(),
            'total' => $journals ? $journals->total() : sizeof($journalsArray),
        ];

        return \response()->json($responseData);
    }
}
