<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ClubController extends Controller
{
    public function index(Request $request)
    {
        $tenants = null;

        if ($request->query('query')) {
            $tenants = Tenant::search($request->query('query'))->query(fn ($query) => $query->with(['tenantOptions' => function ($query) {
                $query->where('Option', 'LOGO_DIR');
            }]))->paginate(config('app.per_page'));
        } else {
            $tenants = Tenant::where('Verified', true)->orderBy('Name', 'asc')->with(['tenantOptions' => function ($query) {
                $query->where('Option', 'LOGO_DIR');
            }])->paginate(config('app.per_page'));
        }

        return Inertia::render('Central/Clubs', [
            'tenants' => $tenants->onEachSide(3),
        ]);
    }

    public function redirect(Tenant $tenant, Request $request)
    {
        return Inertia::location($request->getScheme().'://'.$tenant->Domain);
    }
}
