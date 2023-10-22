<?php

namespace App\Models\Tenant;

use Carbon\Carbon;
use Illuminate\Database\Eloquent\Collection;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property int $id
 * @property string $name
 * @property int $sequence
 * @property Carbon $start_time
 * @property string $timezone
 * @property Carbon $end_time
 * @property Venue $venue
 * @property Competition $competition
 * @property Collection $events
 */
class CompetitionSession extends Model
{
    use HasFactory, BelongsToPrimaryModel;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'start_time' => 'datetime',
        'end_time' => 'datetime',
    ];

    protected $fillable = [
        'name',
        'sequence',
        'start_time',
        'end_time',
        'timezone',
    ];

    /**
     * The relationships that should always be loaded.
     *
     * @var array
     */
    protected $with = ['events'];

    public function competition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function venue(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Venue::class);
    }

    public function events(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompetitionEvent::class)->orderBy('sequence', 'asc');
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'competition';
    }
}
