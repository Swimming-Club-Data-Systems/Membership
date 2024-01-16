<?php

namespace App\Models\Tenant;

use App\Events\Tenant\ProductCreating;
use App\Models\Central\Tenant;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * @property string $id
 * @property string $name
 * @property bool $active
 * @property string $description
 * @property string $stripe_id
 * @property bool $shippable
 * @property string $unit_label
 * @property bool $public
 * @property Collection $prices
 */
class Product extends Model
{
    use BelongsToTenant, HasUuids, Searchable;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'name' => '',
        'description' => '',
        'active' => true,
        'shippable' => false,
        'unit_label' => '',
        'public' => false,
    ];

    protected $casts = [
        'name' => 'string',
        'description' => 'string',
        'active' => 'boolean',
        'shippable' => 'boolean',
        'unit_label' => 'string',
        'public' => 'boolean',
    ];

    protected $fillable = [
        'name',
        'description',
        'active',
        'shippable',
        'unit_label',
        'public',
        'stripe_id',
    ];

    /**
     * The event map for the model.
     *
     * @var array
     */
    protected $dispatchesEvents = [
        'creating' => ProductCreating::class,
    ];

    public static function fromStripe(\Stripe\Product|string $product)
    {
        if (! ($product instanceof \Stripe\Product)) {
            /** @var Tenant $tenant */
            $tenant = tenant();
            $stripe = new \Stripe\StripeClient(config('cashier.secret'));
            $product = $stripe->products->retrieve($product, [
            ], [
                'stripe_account' => $tenant->stripeAccount(),
            ]);
        }

        return self::updateOrCreate([
            'stripe_id' => $product->id,
        ], [
            'name' => $product->name,
            'active' => $product->active,
            'description' => $product->description,
            'shippable' => (bool) $product->shippable,
            'unit_label' => $product->unit_label,
        ]);
    }

    public function prices(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(Price::class);
    }
}
