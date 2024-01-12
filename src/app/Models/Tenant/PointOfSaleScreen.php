<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $name
 * @property Collection $pointOfSaleItemGroups
 */
class PointOfSaleScreen extends Model
{
    use BelongsToTenant, HasUuids;

    protected $with = [
        'pointOfSaleItemGroups',
        'pointOfSaleItemGroups.pointOfSaleItems',
        'pointOfSaleItemGroups.pointOfSaleItems.price',
    ];

    protected $attributes = [

    ];

    protected $fillable = [
        'name',
    ];

    protected $casts = [
        'name' => 'string',
    ];

    public function pointOfSaleItemGroups(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PointOfSaleItemGroup::class)->orderBy('sequence', 'asc');
    }
}
