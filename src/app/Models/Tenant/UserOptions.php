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
class UserOptions extends Model
{
    use HasFactory;

    /**
     * Get the user that owns this option.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'User', 'UserID');
    }

    protected $table = 'userOptions';
    protected $primaryKey = 'ID';
}
