<?php

namespace App\Models\Tenant\Auth;

use App\Traits\UuidIdentifier;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class UserCredential extends Model
{
    use HasFactory, UuidIdentifier;

    /**
     * Get the post that owns the comment.
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
