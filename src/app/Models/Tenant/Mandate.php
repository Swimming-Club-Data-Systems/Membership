<?php

namespace App\Models\Tenant;

use ArrayObject;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * @property int $id
 * @property string $stripe_id
 * @property string $status
 * @property string $type
 * @property PaymentMethod $paymentMethod
 * @property ArrayObject $customer_acceptance
 * @property ArrayObject $pm_type_details
 */
class Mandate extends Model
{
    use HasFactory;

    public function paymentMethod()
    {
        return $this->belongsTo(PaymentMethod::class);
    }

    protected $casts = [
        'customer_acceptance' => AsArrayObject::class,
        'pm_type_details' => AsArrayObject::class,
    ];
}
