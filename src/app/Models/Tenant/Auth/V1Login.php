<?php

namespace App\Models\Tenant\Auth;

use App\Models\Tenant\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\MassPrunable;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property int $id
 * @property int $user_id
 * @property string $token
 */
class V1Login extends Model
{
    use MassPrunable, BelongsToPrimaryModel;

    /**
     * Get the post that owns the comment.
     */
    public function user()
    {
        return $this->belongsTo(User::class, null, 'UserID');
    }

    /**
     * Get the prunable V1Login query.
     *
     * @return \Illuminate\Database\Eloquent\Builder
     */
    public function prunable()
    {
        return static::where('created_at', '<=', now()->subDay());
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'user';
    }
}
