<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property int $ExtraID
 * @property string $ExtraName
 * @property float $ExtraFee
 * @property int $fee
 * @property string $Type
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class ExtraFee extends Model
{
    use BelongsToTenant;

    protected $primaryKey = 'ExtraID';

    protected $table = 'extras';

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
