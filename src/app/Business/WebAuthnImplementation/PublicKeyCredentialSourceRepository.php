<?php

namespace App\Business\WebAuthnImplementation;

use App\Models\Tenant\Auth\UserCredential;
use App\Models\Tenant\User;
use Illuminate\Support\Facades\Auth;
use ParagonIE\ConstantTime\Base64UrlSafe;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialSourceRepository as PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\PublicKeyCredentialUserEntity;

class PublicKeyCredentialSourceRepository implements PublicKeyCredentialSourceRepositoryInterface
{
    public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
    {
        $credential = UserCredential::firstWhere('credential_id', Base64UrlSafe::encodeUnpadded($publicKeyCredentialId));

        if (! $credential) {
            return null;
        }

        return PublicKeyCredentialSource::createFromArray(json_decode($credential->credential, true));
    }

    /**
     * @return PublicKeyCredentialSource[]
     */
    public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
    {
        $sources = [];

        $credentials = UserCredential::where('user_id', $publicKeyCredentialUserEntity->getId())->get();

        foreach ($credentials as $credential) {
            $source = PublicKeyCredentialSource::createFromArray(json_decode($credential->credential, true));
            $sources[] = $source;
        }

        return $sources;
    }

    public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
    {
        // Check if existing
        $credential = UserCredential::firstWhere('credential_id', Base64UrlSafe::encodeUnpadded($publicKeyCredentialSource->getPublicKeyCredentialId()));

        if ($credential) {
            // Update
            $credential->credential = json_encode($publicKeyCredentialSource->jsonSerialize());
            $credential->save();
        } else {
            // Insert
            if (! Auth::id()) {
                return;
            }

            $user = User::find(Auth::id());

            $credential = new UserCredential();
            $credential->credential_id = Base64UrlSafe::encodeUnpadded($publicKeyCredentialSource->getPublicKeyCredentialId());
            $credential->credential = json_encode($publicKeyCredentialSource->jsonSerialize());
            $credential->credential_name = 'FIDO2 Passkey Credential';

            $user->userCredentials()->save($credential);
        }
    }
}
