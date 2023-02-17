<?php

namespace App\Models\Central;

use App\Models\Central\Auth\UserCredential;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

/**
 * @property int id
 * @property string first_name
 * @property string last_name
 * @property string email
 * @property string password
 * @property string gravatar_url
 */
class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'first_name',
        'last_name',
        'email',
        'password',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
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
    protected string $guard = 'central';
    protected $table = 'central_users';
    protected $appends = ['gravatar_url'];

    /**
     * Get the user's profile image url.
     *
     * @return  Attribute
     */
    public function gravatarUrl(): Attribute
    {
        return new Attribute(
            get: fn() => "https://www.gravatar.com/avatar/" . md5(mb_strtolower(trim($this->email))) . "?d=mp",
        );
    }

    /**
     * Get the WebAuthn User Credentials for the user.
     */
    public function userCredentials(): HasMany
    {
        return $this->hasMany(UserCredential::class, 'user_id');
    }

    /**
     * Get the user name via expected attribute.
     *
     * @return Attribute
     */
    protected function name(): Attribute
    {
        return Attribute::make(
            get: fn($value, $attributes) => $attributes['first_name'] . ' ' . $attributes['last_name'],
        );
    }

    public function tenants(): BelongsToMany
    {
        return $this->belongsToMany(Tenant::class, 'central_user_tenant', 'user_id', 'tenant_id', 'id');
    }
}
