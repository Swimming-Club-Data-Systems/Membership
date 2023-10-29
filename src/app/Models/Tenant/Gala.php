<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

/**
 * @property int GalaID
 * @property string GalaName
 * @property string CourseLength
 * @property string GalaVenue
 * @property float GalaFee
 * @property bool GalaFeeConstant
 * @property \DateTime ClosingDate
 * @property \DateTime GalaDate
 * @property bool HyTek
 * @property bool CoachEnters
 * @property bool RequiresApproval
 * @property string Description
 * @property int PaymentCategory
 * @property int ProcessingFee
 */
class Gala extends Model
{
    use BelongsToTenant;

    protected $primaryKey = 'GalaID';

    public function teamManagers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'teamManagers', 'Gala', 'User')
            ->withTimestamps();
    }

    public function entries(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(GalaEntry::class, 'GalaID', 'GalaID');
    }
}
