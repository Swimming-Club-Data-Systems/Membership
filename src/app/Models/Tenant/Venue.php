<?php

namespace App\Models\Tenant;

use App\Business\Helpers\PhoneNumber;
use App\Traits\BelongsToTenant;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Laravel\Scout\Searchable;

/**
 *
 * Model to represent venues (locations) for training sessions, galas and more
 *
 * @property int $id
 * @property string $name
 * @property string $description
 * @property float $long
 * @property float $lat
 * @property string $website
 * @property string $phone
 * @property string $google_maps_url
 * @property string $place_id
 * @property string $plus_code_global
 * @property string $plus_code_compound
 * @property string $vicinity
 * @property string $formatted_address
 * @property array $address_components
 * @property array $html_attributions
 * @property Carbon $created_at
 * @property Carbon $updated_at
 */
class Venue extends Model
{
    use HasFactory, BelongsToTenant, Searchable;

    /**
     * The attributes that should be cast.
     *
     * @var array
     */
    protected $casts = [
        'address_components' => 'array',
        'html_attributions' => 'array',
    ];

    /**
     * The fillable attributes
     *
     * @var array
     */
    protected $fillable = [
        'name',
        'description',
        'long',
        'lat',
        'website',
        'phone',
        'google_maps_url',
        'place_id',
        'plus_code_global',
        'plus_code_compound',
        'vicinity',
        'formatted_address',
        'address_components',
        'html_attributions',
    ];

    /**
     * The model's default values for attributes.
     *
     * @var array
     */
    protected $attributes = [
        'description' => '',
        'address_components' => '[]',
        'html_attributions' => '[]',
    ];

    protected function phone(): Attribute
    {
        return Attribute::make(
            get: fn (mixed $value) => $value ? PhoneNumber::create($value) : null,
            set: fn (mixed $value) => PhoneNumber::toDatabaseFormat($value),
        );
    }


}
