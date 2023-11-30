<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;

/**
 * @property string $ID
 * @property string $Name
 * @property Carbon $StartDate
 * @property string $EndDate
 */
class MembershipYear extends Model
{
    use BelongsToTenant, HasUuids;

    protected $table = 'membershipYear';
}
