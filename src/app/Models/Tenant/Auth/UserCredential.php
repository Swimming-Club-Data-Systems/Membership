<?php

namespace App\Models\Tenant\Auth;

use App\Traits\UuidIdentifier;
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
    use HasFactory, UuidIdentifier;

    protected $table = 'userCredentials';

    /**
     * Get the user that owns the credential.
     */
    public function user()
    {
        return $this->belongsTo(User::class, null, 'UserID');
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'user';
    }
}
