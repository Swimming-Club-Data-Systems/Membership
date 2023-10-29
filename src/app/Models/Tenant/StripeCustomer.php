<?php

namespace App\Models\Tenant;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Stancl\Tenancy\Database\Concerns\BelongsToPrimaryModel;

/**
 * @property int $ID
 * @property int $User
 * @property string $CustomerID
 * @property User $user
 */
class StripeCustomer extends Model
{
    use HasFactory, BelongsToPrimaryModel;

    protected $table = 'stripeCustomers';

    protected $primaryKey = 'ID';

    /**
     * Get the user that owns this option.
     */
    public function user()
    {
        return $this->belongsTo(User::class, 'User', 'UserID');
    }

    public function getRelationshipToPrimaryModel(): string
    {
        return 'user';
    }
}
