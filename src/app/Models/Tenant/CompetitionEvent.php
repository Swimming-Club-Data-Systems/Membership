<?php

namespace App\Models\Tenant;

use App\Enums\DistanceUnits;
use App\Enums\EventCode;
use App\Enums\Stroke;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $id
 * @property string $name
 * @property int $sequence
 * @property Stroke $stroke
 * @property DistanceUnits $units
 * @property int $distance
 * @property EventCode $event_code
 * @property array $ages
 */
class CompetitionEvent extends Model
{
    use HasFactory;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'ages' => 'array',
        'event_code' => EventCode::class,
        'stroke' => Stroke::class,
        'units' => DistanceUnits::class,
    ];

    public function session(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(CompetitionSession::class);
    }

    public function competition(): \Illuminate\Database\Eloquent\Relations\HasOneThrough
    {
        return $this->hasOneThrough(Competition::class, CompetitionSession::class);
    }
}
