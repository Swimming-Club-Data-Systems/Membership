<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

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
}
