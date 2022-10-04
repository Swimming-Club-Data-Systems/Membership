<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @var int $ID
 * @var string $Permission
 * @var int $User
 */
class Permission extends Model
{
    use HasFactory, BelongsToPrimaryModel;

    protected $primaryKey = 'ID';

    /**
     * Get the user
     * @return \Illuminate\Database\Eloquent\Relations\BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class, 'User', 'UserID');
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'user';
    }
}
