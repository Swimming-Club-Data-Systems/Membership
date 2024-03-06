<?php

namespace App\Models\Tenant;

use App\Enums\ClubMembershipClassType;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\AsArrayObject;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Model;

/**
 * @property string $ID
 * @property ClubMembershipClassType $Type
 * @property string $Name
 * @property string $Description
 * @property array $Fees
 */
class ClubMembershipClass extends Model
{
    use BelongsToTenant, HasUuids;

    protected $casts = [
        'Type' => ClubMembershipClassType::class,
        'Fees' => AsArrayObject::class,
    ];

    protected $attributes = [
        'Fees' => '{}',
    ];

    protected $fillable = [
        'Type',
        'Name',
        'Description',
        'Fees',
    ];

    protected $primaryKey = 'ID';

    protected $table = 'clubMembershipClasses';
}
