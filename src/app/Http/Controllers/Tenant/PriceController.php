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

    public function new(Product $product)
    {
        $this->authorize('create', Price::class);

        return Inertia::render('Prices/New', [
            'product' => [
                'id' => $product->id,
                'name' => $product->name,
            ],
        ]);
    }

    public function create(Product $product, Request $request)
    {
        $this->authorize('create', Price::class);

        $validated = $request->validate([
            'unit_amount' => ['decimal:0,2', 'required', 'min:0'],
            'nickname' => ['string', 'max:255', 'required'],
        ]);

        try {

            DB::beginTransaction();

            $price = new Price();
            $price->unit_amount = BigDecimal::of($request->string('unit_amount'))->withPointMovedRight(2)->toInt();
            $price->nickname = $request->string('nickname');

            $product->prices()->save($price);

            DB::commit();

            $request->session()->flash('success', 'Price successfully created.');

            return redirect(route('products.show', $product));
        } catch (ApiConnectionException) {
            DB::rollBack();
            $request->session()->flash('error', 'An error occurred trying to connect to Stripe.');
        } catch (ApiErrorException) {
            DB::rollBack();
            $request->session()->flash('error', 'An error occurred trying to create the Price in Stripe. Please check your Stripe error log.');
        } catch (\Exception $e) {
            DB::rollBack();
            $request->session()->flash('error', $e->getMessage());
        }

        return redirect()->back();
    }
}
