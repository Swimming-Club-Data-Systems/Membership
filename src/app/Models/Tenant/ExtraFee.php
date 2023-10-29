<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;

/**
 * @property int ExtraID
 * @property string ExtraName
 * @property float ExtraFee
 * @property string Type
 * @property Date created_at
 * @property Date updated_at
 */
class ExtraFee extends Model
{
    use BelongsToTenant;

    protected $primaryKey = 'ExtraID';

    public function members()
    {
        return $this->belongsToMany(Member::class, 'extrasRelations', 'ExtraID', 'MemberID')->withTimestamps();
    }

    public function users()
    {
        return $this->belongsToMany(User::class, 'extrasRelations', 'ExtraID', 'UserID')->withTimestamps();
    }

    /**
     * Get or set the fee as an integer.
     */
    protected function fee(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => BigDecimal::of((string) $attributes['ExtraFee'])->withPointMovedRight(2)->toInt(),
            set: fn ($value) => [
                'ExtraFee' => BigDecimal::of($value)->withPointMovedLeft(2),
            ],
        );
    }
}
