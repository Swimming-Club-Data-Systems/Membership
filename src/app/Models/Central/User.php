<?php

namespace App\Models\Central;

use App\Models\Central\Auth\UserCredential;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
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
    protected $appends = ['gravitar_url'];

    /**
     * Get the user's profile image url.
     *
     * @return  Attribute
     */
    public function gravitarUrl(): Attribute
    {
        return new Attribute(
            get: fn() => "https://www.gravatar.com/avatar/" . md5(mb_strtolower(trim($this->email))) . "?d=mp",
        );
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

    /**
     * Get the WebAuthn User Credentials for the user.
     */
    public function userCredentials(): HasMany
    {
        return $this->hasMany(UserCredential::class, 'user_id');
    }
}
