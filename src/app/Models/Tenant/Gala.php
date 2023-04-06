<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use App\Traits\BelongsToTenant;

/**
 * @property int GalaID
 * @property string GalaName
 * @property string CourseLength
 * @property string GalaVenue
 * @property float GalaFee
 * @property boolean GalaFeeConstant
 * @property \DateTime ClosingDate
 * @property \DateTime GalaDate
 * @property boolean HyTek
 * @property boolean CoachEnters
 * @property boolean RequiresApproval
 * @property string Description
 * @property int PaymentCategory
 * @property int ProcessingFee
 */
class Gala extends Model
{
    use HasFactory, BelongsToTenant;

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
