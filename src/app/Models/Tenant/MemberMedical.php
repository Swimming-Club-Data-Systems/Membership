<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property Member $member
 * @property int $ID
 * @property string $Conditions
 * @property string $Allergies
 * @property string $Medication
 * @property string $GPName
 * @property string $GPAddress
 * @property string $GPPhone
 * @property bool $WithholdConsent
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class MemberMedical extends Model
{
    use BelongsToPrimaryModel;

    protected $casts = [
        'WithholdConsent' => 'boolean',
        'GPAddress' => 'json',
    ];

    public function member(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(Member::class, 'MemberID');
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'member';
    }

    protected $primaryKey = 'ID';

    protected $table = 'memberMedical';
}
