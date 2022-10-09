<?php

namespace App\Models\Central;

use App\Models\Tenant\TenantOption;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Laravel\Scout\Searchable;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * @property int ID
 * @property string Name
 * @property string Code
 * @property string Website
 * @property string Email
 * @property boolean Verified
 * @property string UniqueID
 * @property string Domain
 * @property string Data
 */
class Tenant extends BaseTenant
{
    use HasDomains, Searchable;

    protected $configOptionsCached = false;
    protected $configOptions = [];
    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = ['tenantOptions', 'Email'];
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['logo_path'];

    public static function getCustomColumns(): array
    {
        return [
            'ID',
            'Name',
            'Code',
            'Website',
            'Email',
            'Verified',
            'UniqueID',
            'Domain',
            'created_at',
            'updated_at',
            'Data'
        ];
    }

    public static function getDataColumn(): string
    {
        return 'Data';
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        // static::retrieved(function ($model) {
        //    foreach ($model->tenantOptions as $option) {
        //        // $model->configOptions[] = $option;
        //        $model->setAttribute($option->Option, $option->Value);
        //        $model->syncOriginalAttribute($option->Option);
        //    }
        //    // ddd($model);
        //});

        // static::retrieved(function ($tenant) {
        //     foreach ($tenant->tenantOptions as $option) {
        //         $tenant->configOptions[] = $option;
        //     }
        //     ddd($tenant);
        // });
    }

    public function getIncrementing()
    {
        return true;
    }

    /**
     * Get the indexable data array for the model.
     *
     * @return array
     */
    public function toSearchableArray()
    {
        return [
            'ID' => $this->ID,
            'Name' => $this->Name,
            'Website' => $this->Website,
            'Domain' => $this->Domain,
        ];
    }

    /**
     * Determine if the model should be searchable.
     *
     * @return bool
     */
    public function shouldBeSearchable()
    {
        return $this->Verified;
    }

    /**
     * Get the tenant id via expected attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['ID'],
        );
    }

    /**
     * Get the tenant logo path.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function logoPath(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $this->getOption("LOGO_DIR") ? getUploadedAssetUrl($this->getOption("LOGO_DIR")) : null,
        );
    }

    public function getOption($key)
    {
        if (!$this->configOptionsCached) {
            foreach ($this->tenantOptions()->get() as $option) {
                $this->configOptions[$option->Option] = $option->Value;
            }
            $this->configOptionsCached = true;
        }

        if (isset($this->configOptions[$key])) {
            return $this->configOptions[$key];
        }

        return null;
    }

    public function setOption($key, $value)
    {
        // Make sure values are cached
        $this->getOption($key);

        // Create or update
        $option = $this->tenantOptions()->where('Option', $key)->firstOrNew();
        $option->Option = $key;
        $option->Value = $value;
        $option->save();

        // Update cache
        $this->configOptions[$key] = $value;
    }

    /**
     * Get the TenantOption.
     */
    public function tenantOptions()
    {
        return $this->hasMany(TenantOption::class, 'Tenant');
    }
}
