<?php

namespace App\Http\Controllers\Tenant;

use App\Http\Controllers\Controller;
use App\Models\Tenant\Price;
use App\Models\Tenant\Product;
use Brick\Math\BigDecimal;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Inertia\Inertia;
use Stripe\Exception\ApiConnectionException;
use Stripe\Exception\ApiErrorException;

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
        $this->authorize('viewAll', Product::class);

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
        $this->authorize('create', Product::class);

        return Inertia::render('Products/New', [

        ]);
    }

    public function create(Request $request)
    {
        $this->authorize('create', Product::class);

        $validated = $request->validate([
            'name' => ['string', 'max:255', 'required'],
            'description' => ['string', 'max:1024', 'required'],
            'shippable' => ['boolean'],
            'unit_label' => ['string', 'max:255', 'required'],
            'unit_amount' => ['decimal:0,2', 'required', 'min:0'],
            'nickname' => ['string', 'max:255', 'required'],
        ]);

        try {

            DB::beginTransaction();

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

            DB::commit();

            $request->session()->flash('success', 'Product successfully created.');

            return redirect(route('products.show', $product));
        } catch (ApiConnectionException) {
            DB::rollBack();
            $request->session()->flash('error', 'An error occurred trying to connect to Stripe.');
        } catch (ApiErrorException) {
            DB::rollBack();
            $request->session()->flash('error', 'An error occurred trying to create the Product or Price in Stripe. Please check your Stripe error log.');
        } catch (\Exception $e) {
            DB::rollBack();
            $request->session()->flash('error', $e->getMessage());
        }

        return redirect()->back();
    }

    public function show(Product $product)
    {
        $this->authorize('view', $product);

        return Inertia::render('Products/Show', [
            'name' => $product->name,
            'id' => $product->id,
            'prices' => $product->prices,
            'unit_label' => $product->unit_label ?? 'each',
        ]);
    }
}
