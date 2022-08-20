<?php

namespace App\Models\Tenant;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Stancl\Tenancy\Database\Concerns\BelongsToTenant;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasApiTokens, HasFactory, Notifiable, BelongsToTenant;

    protected $configOptionsCached = false;
    protected $configOptions = [];

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

    public function getOption($key)
    {
        if (!$this->configOptionsCached) {
            foreach ($this->userOptions as $option) {
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
     * Relationships
     */

    /**
     * Get the V1Logins for the user.
     */
    public function v1Logins()
    {
        return $this->hasMany(Auth\V1Login::class, 'user_id');
    }

    /**
     * Get the WebAuthn User Credentials for the user.
     */
    public function userCredentials()
    {
        return $this->hasMany(Auth\UserCredential::class, 'user_id');
    }

    /**
     * Get the options for the user.
     */
    public function userOptions()
    {
        return $this->hasMany(UserOptions::class, 'User');
    }

    /**
     * Auth stuff
     */
    public function getAuthIdentifierName()
    {
        return "UserID";
    }

    public function getAuthIdentifier()
    {
        return $this->UserID;
    }

    public function getAuthPassword()
    {
        return $this->Password;
    }

    /**
     * Get the user id via expected attribute.
     *
     * @return \Illuminate\Database\Eloquent\Casts\Attribute
     */
    protected function id(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['UserID'],
        );
    }

    protected $primaryKey = 'UserID';

    public function getEmailForPasswordReset()
    {
        return $this->EmailAddress;
    }

    public function getEmailAttribute()
    {
        return $this->attributes['EmailAddress'];
    }

    protected function email(): Attribute
    {
        return Attribute::make(
            get: fn ($value, $attributes) => $attributes['EmailAddress'],
            set: fn ($value) => [
                'EmailAddress' => $value,
            ],
        );
    }

    /**
     * Get the user's profile image url.
     *
     * @return  \Illuminate\Database\Eloquent\Casts\Attribute
     */
    public function gravitarUrl(): Attribute
    {
        return new Attribute(
            get: fn () => "https://www.gravatar.com/avatar/" . md5(mb_strtolower(trim($this->EmailAddress))) . "?d=mp",
        );
    }

    /**
     * The accessors to append to the model's array form.
     *
     * @var array
     */
    protected $appends = ['gravitar_url'];
}
