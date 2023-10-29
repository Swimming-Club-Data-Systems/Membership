<?php

namespace App\Models\Tenant;

use App\Traits\BelongsToTenant;
use App\Traits\UuidIdentifier;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $ID
 * @property string $Name
 * @property string $Description
 * @property bool $Active
 */
class NotifyCategory extends Model
{
    use BelongsToTenant, UuidIdentifier;

    protected $primaryKey = 'ID';

    protected $table = 'notifyCategories';

    /**
     * Get the user's notify category options
     */
    public function users()
    {
        return $this->belongsToMany(User::class, 'notifyOptions', 'EmailType', 'UserID')
            ->as('subscription')
            ->withTimestamps()
            ->withPivot([
                'Subscribed',
            ]);
    }
}
