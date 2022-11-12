<?php

namespace App\Models\Tenant;

use ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;

/**
 * @property int $id
 * @property string $stripe_id
 * @property string $type
 * @property User $user
 * @property ArrayObject $pm_type_data
 * @property ArrayObject $billing_address
 */
class PaymentMethod extends Model
{
    use HasFactory, SoftDeletes;

    public function user(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function mandate(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(Mandate::class);
    }

    protected $casts = [
        'pm_type_data' => AsArrayObject::class,
        'billing_address' => AsArrayObject::class,
    ];
}
