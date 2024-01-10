<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $point_of_sale_item_group_id
 * @property PointOfSaleItemGroup $pointOfSaleItemGroup
 * @property string $label
 * @property int $sequence
 * @property string $price_id
 * @property Price $price
 */
class PointOfSaleItem extends Model
{
    use HasUuids;

    protected $attributes = [

    ];

    protected $fillable = [
        'label',
        'sequence',
    ];

    protected $casts = [
        'label' => 'string',
        'sequence' => 'int',
    ];

    public function pointOfSaleItemGroup(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PointOfSaleItemGroup::class);
    }

    public function price(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Price::class);
    }
}
