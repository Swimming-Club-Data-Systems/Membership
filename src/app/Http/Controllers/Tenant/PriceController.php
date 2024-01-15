<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Price;
use App\Models\Tenant\Product;
use Inertia\Inertia;

class PriceController extends Controller
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

    public function index(Product $product)
    {
        return Inertia::render('', [

        ]);
    }

    public function new(Product $product)
    {
        return Inertia::render('', [

        ]);
    }

    public function create(Product $product)
    {

    }

    public function show(Product $product, Price $price)
    {
        return Inertia::render('', [

        ]);
    }
}
