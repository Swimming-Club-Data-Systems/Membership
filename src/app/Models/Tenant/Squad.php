<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Brick\Math\BigDecimal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Laravel\Scout\Searchable;

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
    use BelongsToTenant, Searchable;

    protected $attributes = [
        'SquadCoach' => '',
        'SquadTimetable' => '',
        'SquadCoC' => '',
        'SquadKey' => '',
    ];

    protected $primaryKey = 'SquadID';

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'squadMembers', 'Squad', 'Member')
            ->withTimestamps()
            ->withPivot([
                'Paying',
            ]);
    }

    public function reps(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'squadReps', 'Squad', 'User')
            ->withTimestamps()
            ->withPivot([
                'ContactDescription',
            ]);
    }

    public function pendingJoiners(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'squadMoves', 'New', 'Member')
            ->using(SquadMove::class);
    }

    public function pendingLeavers(): BelongsToMany
    {
        return $this->belongsToMany(Member::class, 'squadMoves', 'Old', 'Member')
            ->using(SquadMove::class);
    }

    public function coaches(): BelongsToMany
    {
        return $this
            ->belongsToMany(User::class, 'coaches', 'Squad', 'User')
            ->withPivot(['Type'])
            ->using(Coach::class);
    }

    /**
     * Get or set the fee as an integer.
     */
    protected function fee(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => BigDecimal::of((string) $attributes['SquadFee'])->withPointMovedRight(2)->toInt(),
            set: fn ($value) => [
                'SquadFee' => BigDecimal::of($value)->withPointMovedLeft(2),
            ],
        );
    }

    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $fields = [
            'SquadID',
            'SquadName',
            'SquadFee',
            'Tenant',
        ];

        return array_intersect_key($array, array_flip($fields));
    }
}
