<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $ID
 * @property int $UserID
 * @property string $Name
 * @property string $ContactNumber
 * @property string $Relation
 */
class EmergencyContact extends Model
{
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'UserID', 'UserID');
    }

    protected $primaryKey = 'ID';

    protected $table = 'emergencyContacts';
}
