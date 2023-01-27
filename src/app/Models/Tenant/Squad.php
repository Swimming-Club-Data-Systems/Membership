<?php

namespace App\Models\Tenant;

use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int SquadID
 * @property string SquadName
 * @property float SquadFee
 * @property string SquadCoach
 * @property string SquadTimetable
 * @property string SquadCoC
 * @property string SquadKey
 * @property int fee the monthly fee for the squad
 */
class Squad extends Model
{
    use HasFactory, BelongsToTenant;

    protected $primaryKey = 'SquadID';

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'squadMembers', 'Squad', 'Member')
            ->withTimestamps()
            ->withPivot([
                'Paying'
            ]);
    }

    public function reps(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'squadReps', 'Squad', 'User')
            ->withTimestamps()
            ->withPivot([
                'ContactDescription'
            ]);
    }

    /**
     * Get or set the fee as an integer.
     *
     * @return Attribute
     */
    protected function fee(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => BigDecimal::of((string)$attributes['SquadFee'])->withPointMovedRight(2)->toInt(),
            set: fn($value) => [
                'SquadFee' => BigDecimal::of($value)->withPointMovedLeft(2)
            ],
        );
    }
}
