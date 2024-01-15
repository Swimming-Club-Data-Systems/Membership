<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Price;
use App\Models\Tenant\Product;
use Brick\Math\BigDecimal;
use Illuminate\Http\Request;
use Inertia\Inertia;

class ProductController extends Controller
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

    public function index(Request $request)
    {
        $products = null;
        if ($request->query('query')) {
            $products = Product::search($request->query('query'))->where('Tenant', tenant('ID'))->paginate(config('app.per_page'));
        } else {
            $products = Product::orderBy('name', 'asc')->paginate(config('app.per_page'));
        }

        return Inertia::render('Products/Index', [
            'products' => $products->onEachSide(3),
        ]);
    }

    public function new()
    {
        return Inertia::render('Products/New', [

        ]);
    }

    public function create(Request $request)
    {
        $validated = $request->validate([
            'name' => ['string', 'max:255', 'required'],
            'description' => ['string', 'max:1024', 'required'],
            'shippable' => ['boolean'],
            'unit_label' => ['string', 'max:255', 'required'],
            'unit_amount' => ['decimal:0,2', 'required', 'min:0'],
            'nickname' => ['string', 'max:255', 'required'],
        ]);

        $product = new Product();
        $product->name = $request->string('name');
        $product->description = $request->string('description');
        $product->shippable = $request->boolean('shippable');
        $product->unit_label = $request->string('unit_label');
        $product->save();

        $price = new Price();
        $price->unit_amount = BigDecimal::of($request->string('unit_amount'))->withPointMovedRight(2)->toInt();
        $price->nickname = $request->string('nickname');

        $product->prices()->save($price);

        return redirect(route('products.show', $product));
    }

    public function show(Product $product)
    {
        return Inertia::render('Products/Show', [

        ]);
    }
}
