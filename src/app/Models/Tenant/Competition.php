<?php

namespace App\Models\Tenant;

use App\Enums\CompetitionCourse;
use App\Traits\BelongsToTenant;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 * @property int $id
 * @property string $name
 * @property string $description
 * @property CompetitionCourse $pool_course
 * @property Venue $venue
 * @property boolean $require_times
 * @property boolean $coach_enters
 * @property boolean $requires_approval
 * @property boolean $public
 * @property int $processing_fee
 * @property Carbon $closing_date
 * @property Carbon $gala_date
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Competition extends Model
{
    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'pool_course' => CompetitionCourse::class,
        'closing_date' => 'datetime',
        'gala_date' => 'datetime',
    ];

    use HasFactory, Searchable, BelongsToTenant;

    public function sessions(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(CompetitionSession::class);
    }

    public function events(): \Illuminate\Database\Eloquent\Relations\HasManyThrough
    {
        return $this->hasManyThrough(CompetitionEvent::class, CompetitionSession::class);
    }
}
