<?php

namespace App\Business\Helpers;

use App\Models\Central\Tenant;
use League\OAuth2\Client\Provider\GenericProvider;

class OAuthLogin
{
    public static function getProvider(string $redirectUri = null): GenericProvider
    {
        /**
         * @var Tenant $tenant;
         */
        $tenant = tenant();

        if (!$tenant->getOption('TENANT_ENABLE_STAFF_OAUTH')) {
            abort(404);
        }

        if (!$redirectUri) {
            $redirectUri = route('login.oauth_verify');
        }

        $provider = new GenericProvider([
            'clientId'                => $tenant->getOption('TENANT_OAUTH_CLIENT_ID'),    // The client ID assigned to you by the provider
            'clientSecret'            => $tenant->getOption('TENANT_OAUTH_CLIENT_SECRET'),    // The client password assigned to you by the provider
            'redirectUri'             => $redirectUri,
            'urlAuthorize'            => $tenant->getOption('TENANT_OAUTH_URL_AUTHORIZE'),
            'urlAccessToken'          => $tenant->getOption('TENANT_OAUTH_URL_ACCESS_TOKEN'),
            'urlResourceOwnerDetails' => '',
            'scopes'                  => 'openid profile offline_access user.read'
        ]);

        return $provider;
    }
}
