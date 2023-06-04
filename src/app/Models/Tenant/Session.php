<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int SessionID
 * @property int VenueID
 * @property string SessionName
 * @property int SessionDay
 * @property \DateTime StartTime
 * @property \DateTime EndTime
 * @property \DateTime DisplayFrom
 * @property \DateTime DisplayUntil
 */
class Session extends Model
{
    use HasFactory, BelongsToTenant;

    public function squads(): BelongsToMany
    {
        return $this->belongsToMany(Squad::class, 'sessionsSquads', 'Session', 'Squad')
            ->withTimestamps()
            ->withPivot([
                'ForAllMembers',
            ]);
    }

    public function venue(): HasOne
    {
        return $this->hasOne(SessionVenue::class, 'VenueID', 'VenueID');
    }

    protected $primaryKey = 'SessionID';
}
