<?php

namespace App\Models\Tenant;

use App\Enums\CompetitionCourse;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property int $id
 * @property string $name
 * @property int $sequence
 * @property Carbon $start_time
 * @property Carbon $end_time
 * @property Venue $venue
 * @property Competition $competition
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

    public function competition(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Competition::class);
    }

    public function events(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompetitionEvent::class);
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'competition';
    }
}
