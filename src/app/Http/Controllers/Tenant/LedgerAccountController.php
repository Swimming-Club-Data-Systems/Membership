<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\LedgerAccount;
use Inertia\Inertia;

class LedgerAccountController extends Controller
{
    public function index()
    {
        $ledgers = LedgerAccount::orderBy('name', 'asc')->paginate(config('app.per_page'));

        return Inertia::render('Payments/Ledgers/Index', [
            'ledgers' => $ledgers->onEachSide(3),
        ]);
    }
}
