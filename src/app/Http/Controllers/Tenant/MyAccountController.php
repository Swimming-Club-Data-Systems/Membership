<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Inertia\Inertia;

class MyAccountController extends Controller
{
    /**
     * Instantiate a new controller instance.
     *
     * @return void
     */
    public function __construct()
    {
        $this->middleware('auth');
        $this->middleware('password.confirm');
    }

    public function show(Request $request)
    {
        return Inertia::render('MyAccount/Show', []);
    }

    public function save(Request $request)
    {
        return Inertia::render('', []);
    }
}
