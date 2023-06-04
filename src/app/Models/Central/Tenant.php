<?php

namespace App\Models\Central;

use App\Exceptions\NoStripeAccountException;
use App\Models\Accounting\Journal;
use App\Models\Tenant\TenantOption;
use App\Traits\Accounting\AccountingJournal;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use function Illuminate\Events\queueable;
use Laravel\Cashier\Billable;
use Laravel\Scout\Searchable;
use Stancl\Tenancy\Database\Concerns\HasDomains;
use Stancl\Tenancy\Database\Models\Tenant as BaseTenant;

/**
 * @property int ID
 * @property string Name
 * @property string Code
 * @property string Website
 * @property string Email
 * @property bool Verified
 * @property string UniqueID
 * @property string Domain
 * @property string Data
 * @property string stripe_id
 * @property string pm_type
 * @property string pm_last_four
 * @property \DateTime trial_ends_at
 * @property string alphanumeric_sender_id
 * @property Journal journal
 * @property string application_fee_type
 * @property float application_fee_amount
 */
class Tenant extends BaseTenant
{
    use HasDomains, Searchable, Billable, AccountingJournal;

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

    protected $primaryKey = 'ID';

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
            'Data',
            'stripe_id',
            'pm_type',
            'pm_last_four',
            'trial_ends_at',
        ];
    }

    public static function getDataColumn(): string
    {
        return 'Data';
    }

    public static function findByStripeAccountId(string $id): Tenant
    {
        /** @var TenantOption $tenantOption */
        $tenantOption = TenantOption::where('Option', 'STRIPE_ACCOUNT_ID')->where('Value', $id)->with(['tenant'])->first();

        if ($tenantOption) {
            return $tenantOption->tenant;
        }
        throw new \Exception('No tenant found for Stripe Account ID: '.$id);
    }

    /**
     * The "booted" method of the model.
     *
     * @return void
     */
    protected static function booted()
    {
        static::updated(queueable(function (Tenant $customer) {
            if ($customer->hasStripeId()) {
                $customer->syncStripeCustomerDetails();
            }
        }));
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

    public function syncStripeCustomerDetails()
    {
        return $this->updateStripeCustomer([
            'name' => $this->stripeName(),
            'email' => $this->stripeEmail(),
            'phone' => $this->stripePhone(),
            'address' => $this->stripeAddress(),
            'preferred_locales' => $this->stripePreferredLocales(),
            'invoice_settings' => [
                'custom_fields' => [
                    [
                        'name' => 'Tenant ID',
                        'value' => $this->ID,
                    ],
                    [
                        'name' => 'Swim England Club Code',
                        'value' => $this->Code,
                    ],
                ],
            ],
        ]);
    }

    public function stripeName()
    {
        return $this->Name;
    }

    public function stripeEmail()
    {
        return $this->Email;
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

    public function getOption($key)
    {
        if (! $this->configOptionsCached) {
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

    /**
     * Get the TenantOption.
     */
    public function tenantOptions()
    {
        return $this->hasMany(TenantOption::class, 'Tenant');
    }

    public function centralUsers(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'central_user_tenant', 'tenant_id', 'user_id');
    }

    /**
     * Return the ID of the tenant's Stripe account, or null if they do not have one
     *
     * @throws NoStripeAccountException
     */
    public function stripeAccount(): ?string
    {
        $accountId = $this->getOption('STRIPE_ACCOUNT_ID');
        if ($accountId) {
            return $accountId;
        }
        throw new NoStripeAccountException();
    }

    public function domains(): \Illuminate\Database\Eloquent\Relations\HasMany
    {
        return $this->hasMany(config('tenancy.domain_model'), 'tenant_id');
    }

    /**
     * Get the tenant id via expected attribute.
     */
    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['ID'] ?? null,
        );
    }

    /**
     * Get the tenant logo path.
     */
    protected function logoPath(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $this->getOption('LOGO_DIR') ? getUploadedAssetUrl($this->getOption('LOGO_DIR')) : null,
        );
    }
}
