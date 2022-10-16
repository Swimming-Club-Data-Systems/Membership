<?php

namespace App\Models\Central\Auth;

use App\Models\Central\User;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $id
 * @property string $credential_id
 * @property string $credential_name
 * @property int $user_id
 * @property string $credential
 */
class UserCredential extends Model
{
    use HasFactory;

    protected $table = 'central_user_credentials';

    /**
     * Get the user that owns the credential.
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
