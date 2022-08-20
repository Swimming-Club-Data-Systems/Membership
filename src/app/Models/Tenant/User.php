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
}
