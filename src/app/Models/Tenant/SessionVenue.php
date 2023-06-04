<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int VenueID
 * @property string VenueName
 * @property string Location
 */
class SessionVenue extends Model
{
    use HasFactory, BelongsToTenant;

    public function sessions(): HasMany
    {
        return $this->hasMany(Session::class, 'SessionID');
    }

    protected $primaryKey = 'SessionID';

    protected $table = 'sessionsVenues';
}
