<?php

namespace App\Models\Tenant;

use App\Enums\StripePriceBillingScheme;
use App\Enums\StripePriceTaxBehavior;
use App\Enums\StripePriceType;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

class Price extends Model
{
    use BelongsToPrimaryModel, HasUuids;

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'currency' => 'gbp',
        'active' => true,
        'nickname' => '',
        'type' => StripePriceType::ONE_TIME,
        'billing_scheme' => StripePriceBillingScheme::PER_UNIT,
        'tax_behavior' => StripePriceTaxBehavior::UNSPECIFIED,
        'usable_in_membership' => true,
    ];

    protected $casts = [
        'currency' => 'string',
        'active' => 'boolean',
        'nickname' => 'string',
        'type' => StripePriceType::class,
        'billing_scheme' => StripePriceBillingScheme::class,
        'tax_behavior' => StripePriceTaxBehavior::class,
        'usable_in_membership' => 'boolean',
    ];

    protected $fillable = [
        'currency',
        'active',
        'nickname',
        'type',
        'unit_amount',
        'billing_scheme',
        'tax_behavior',
        'usable_in_membership',
        'stripe_id',
    ];

    public function product(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Product::class);
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'product';
    }
}
