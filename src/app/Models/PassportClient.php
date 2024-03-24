<?php

namespace App\Models;

use Laravel\Passport\Client;

class PassportClient extends Client
{
    protected $attributes = [
        'first_party' => false,
    ];

    protected $casts = [
        'grant_types' => 'array',
        'scopes' => 'array',
        'personal_access_client' => 'bool',
        'password_client' => 'bool',
        'revoked' => 'bool',
        'first_party' => 'bool',
    ];

    /**
     * Determine if the client should skip the authorization prompt.
     *
     * @return bool
     */
    public function skipsAuthorization()
    {
        return $this->first_party || parent::skipsAuthorization();
    }
}
