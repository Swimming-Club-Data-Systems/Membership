<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ID
 * @property int $UserID
 * @property string $EmailAddress
 * @property string $Name
 * @property string $Hash
 * @property bool $Verified
 */
class NotifyAdditionalEmail extends Model
{
    use HasFactory;

    protected $primaryKey = 'ID';

    protected $table = 'notifyAdditionalEmails';

    /**
     * Get the user that owns this option.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'User', 'UserID');
    }
}
