<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

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
    use BelongsToTenant;

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
