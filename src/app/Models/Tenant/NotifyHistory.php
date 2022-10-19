<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Date;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int $ID
 * @property int $Sender
 * @property string $Subject
 * @property string $Message
 * @property bool $ForceSend
 * @property Date $Date
 * @property $JSONData
 */
class NotifyHistory extends Model
{
    use HasFactory, BelongsToTenant;

    protected $primaryKey = 'ID';
    protected $table = 'notifyHistory';

    public function author()
    {
        return $this->hasMany(User::class, 'Sender', 'UserID');
    }
}
