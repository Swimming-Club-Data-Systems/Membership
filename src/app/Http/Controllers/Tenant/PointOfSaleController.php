<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use Inertia\Inertia;

class PointOfSaleController extends Controller
{
    public function index()
    {
        return Inertia::render('Payments/PointOfSale/PointOfSale', []);
    }
}
