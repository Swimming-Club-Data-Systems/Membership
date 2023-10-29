<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * @property int VenueID
 * @property string VenueName
 * @property string Location
 */
class SessionVenue extends Model
{
    use BelongsToTenant;

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'SessionID');
    }

    protected $primaryKey = 'SessionID';

    protected $table = 'sessionsVenues';
}
