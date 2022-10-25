<?php

namespace App\Http\Controllers\Central;

use App\Http\Controllers\Controller;
use App\Models\Central\Tenant;
use Illuminate\Http\Request;
use Inertia\Inertia;

class TenantAdministratorsController extends Controller
{
    public function index(Tenant $tenant)
    {
        return Inertia::render('Central/Tenants/Administrators', [
            'id' => $tenant->ID,
            'name' => $tenant->Name,
        ]);
    }
}
