<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property int $ID
 * @property int $User
 * @property string $Option
 * @property string $Value
 */
class UserOption extends Model
{
    use HasFactory;

    protected $table = 'userOptions';

    protected $primaryKey = 'ID';

    /**
     * Get the user that owns this option.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'User', 'UserID');
    }
}
