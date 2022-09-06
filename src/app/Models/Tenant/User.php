<?php

namespace App\Models\Tenant;

use App\Business\Helpers\Address;
use App\Mail\VerifyEmailChange;
use App\Models\Tenant\Auth\UserCredential;
use App\Models\Tenant\Auth\V1Login;
use Illuminate\Contracts\Auth\MustVerifyEmail;
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
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;
use Illuminate\Database\Eloquent\Builder;

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
    use HasApiTokens, HasFactory, Notifiable, BelongsToTenant;

    protected bool $configOptionsCached = false;
    protected array $configOptions = [];

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

    /**
     * Get the user's assigned permissions
     * @return HasMany
     */
    public function permissions(): HasMany
    {
        return $this->hasMany(Permission::class, 'User');
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
