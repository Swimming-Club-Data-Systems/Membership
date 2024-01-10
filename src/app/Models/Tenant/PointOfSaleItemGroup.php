<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property string $id
 * @property string $point_of_sale_screen_id
 * @property string $name
 * @property int $sequence
 * @property PointOfSaleScreen $pointOfSaleScreen
 */
class PointOfSaleItemGroup extends Model
{
    use BelongsToPrimaryModel, HasUuids;

    protected $attributes = [

    ];

    protected $fillable = [
        'name',
        'sequence',
    ];

    protected $casts = [
        'name' => 'string',
        'sequence' => 'int',
    ];

    public function pointOfSaleItems(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(PointOfSaleItem::class);
    }

    public function pointOfSaleScreen(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(PointOfSaleScreen::class);
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'pointOfSaleScreen';
    }
}
