<?php

namespace App\Models\Tenant;

use App\Business\Helpers\Address;
use App\Mail\VerifyEmailChange;
use App\Models\Tenant\Auth\UserCredential;
use App\Models\Tenant\Auth\V1Login;
use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use Laravel\Passport\HasApiTokens;
use Laravel\Scout\Searchable;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

/**
 * @property int $UserID
 * @property string $Forename
 * @property string $Surname
 * @property string $Password
 * @property string $EmailAddress
 * @property bool $EmailComms
 * @property string $Mobile
 * @property bool $MobileComms
 * @property bool $Active
 * @property string $name
 */
class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, BelongsToTenant, Searchable;

    protected bool $configOptionsCached = false;
    protected array $configOptions = [];
    protected bool $permissionsCached = false;
    protected array $permissionsCache = [];

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'Forename',
        'Surname',
        'EmailAddress',
        'Password'
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'Password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
    protected $casts = [
        'email_verified_at' => 'datetime',
    ];
    protected $primaryKey = 'UserID';
    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['gravitar_url'];

    protected static function booted()
    {
        static::addGlobalScope('active', function (Builder $builder) {
            $builder->where('Active', true);
        });
    }

    /**
     * Send an email to the new address to validate and change
     *
     * @param string $email
     * @return void
     */
    public function verifyNewEmail(string $email): void
    {
        // User has changed their email
        // The email is not already in use for this tenant
        // Send a signed link to the new email to confirm
        $url = URL::temporarySignedRoute(
            'verification.verify_change',
            now()->addDay(),
            ['user' => Auth::id(), 'email' => Str::lower($email)]
        );

        $recipient = new \stdClass();
        $recipient->email = $email;
        $recipient->name = $this->name;

        Mail::to($recipient)->send(new VerifyEmailChange($this, $url, $email));
    }

    /**
     * Get the user id via expected attribute.
     *
     * @return Attribute
     */
    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['UserID'],
        );
    }

    /**
     * Relationships
     */

    public function setOption($key, $value)
    {
        // Make sure values are cached
        $this->getOption($key);

        // Create or update
        $option = $this->userOptions()->where('Option', $key)->firstOrNew();
        $option->Option = $key;
        $option->Value = $value;
        $option->save();

        // Update cache
        $this->configOptions[$key] = $value;
    }

    public function getOption($key)
    {
        if (!$this->configOptionsCached) {
            foreach ($this->userOptions()->get() as $option) {
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
     * Get the options for the user.
     */
    public function userOptions(): HasMany
    {
        return $this->hasMany(UserOption::class, 'User');
    }

    /**
     * Get the V1Logins for the user.
     */
    public function v1Logins(): HasMany
    {
        return $this->hasMany(V1Login::class, 'user_id');
    }

    /**
     * Get the WebAuthn User Credentials for the user.
     */
    public function userCredentials(): HasMany
    {
        return $this->hasMany(UserCredential::class, 'user_id');
    }

    /**
     * Get the user's notify category options
     */
    public function notifyCategories(): BelongsToMany
    {
        return $this->belongsToMany(NotifyCategory::class, 'notifyOptions', 'UserID', 'EmailType', 'ID')
            ->as('subscription')
            ->withTimestamps()
            ->withPivot([
                'Subscribed'
            ]);
    }

    /**
     * Get the additional emails for the user.
     */
    public function notifyAdditionalEmails(): HasMany
    {
        return $this->hasMany(NotifyAdditionalEmail::class, 'UserID');
    }

    public function members(): HasMany
    {
        return $this->hasMany(Member::class, 'UserID');
    }

    public function representedSquads(): BelongsToMany
    {
        return $this->belongsToMany(Squad::class, 'squadReps', 'User', 'Squad')
            ->withTimestamps()
            ->withPivot([
                'ContactDescription'
            ]);
    }

    public function galas(): BelongsToMany
    {
        return $this->belongsToMany(Gala::class, 'teamManagers', 'User', 'Gala')
            ->withTimestamps();
    }

    public function hasPermission(string|array $name)
    {
        // Fetch cache
        if (!$this->permissionsCached) {
            foreach ($this->permissions()->get() as $permission) {
                $this->permissionsCache[] = $permission->Permission;
            }
            $this->permissionsCached = true;
        }

        if (gettype($name) == 'string') {
            return in_array($name, $this->permissionsCache);
        } else if (gettype($name) == 'array') {
            foreach ($name as $item) {
                if (in_array($item, $this->permissionsCache)) {
                    return true;
                }
            }
        }

        return false;
    }

    /**
     * Get the user's assigned permissions
     * @return HasMany
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'User');
    }

    public function onboardingSessions()
    {
        return $this->hasMany(OnboardingSession::class, 'user', 'UserID');
    }

    /**
     * Auth stuff
     */
    public function getAuthIdentifierName(): string
    {
        return "UserID";
    }

    public function getAuthIdentifier(): int
    {
        return $this->UserID;
    }

    public function getAuthPassword(): string
    {
        return $this->Password;
    }

    public function getEmailForPasswordReset(): string
    {
        return $this->EmailAddress;
    }

    public function getEmailAttribute()
    {
        return $this->attributes['EmailAddress'];
    }

    /**
     * get an Address object for the user
     * @return Address
     */
    public function getAddress(): Address
    {
        return Address::create($this->getOption('MAIN_ADDRESS'));
    }

    /**
     * Get the user's profile image url.
     *
     * @return  Attribute
     */
    public function gravitarUrl(): Attribute
    {
        return new Attribute(
            get: fn() => "https://www.gravatar.com/avatar/" . md5(mb_strtolower(trim($this->EmailAddress))) . "?d=mp",
        );
    }

    public function getEmailForVerification(): string
    {
        return $this->EmailAddress;
    }

    /**
     * Determine if the model should be searchable.
     *
     * @return bool
     */
    public function shouldBeSearchable()
    {
        return $this->Active;
    }

    public function toSearchableArray(): array
    {
        $array = $this->toArray();

        $fields = [
            'UserID',
            'Forename',
            'Surname',
            'EmailAddress',
            'Mobile',
            'Tenant',
        ];

        return array_intersect_key($array, array_flip($fields));
    }

    /**
     * Get the user name via expected attribute.
     *
     * @return Attribute
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['Forename'] . ' ' . $attributes['Surname'],
        );
    }

    /**
     * Get the user's password via expected attribute.
     *
     * @return Attribute
     */
    protected function password(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['Password'],
        );
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['EmailAddress'],
            set: fn($value) => [
                'EmailAddress' => $value,
            ],
        );
    }
}
