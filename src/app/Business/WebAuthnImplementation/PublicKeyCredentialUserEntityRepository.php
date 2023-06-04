<?php

namespace App\Business\WebAuthnImplementation;

use App\Models\Tenant\User;
use Webauthn\PublicKeyCredentialEntity;
use Webauthn\PublicKeyCredentialUserEntity;

final class PublicKeyCredentialUserEntityRepository
{
    public function findWebauthnUserByUsername(string $username): ?PublicKeyCredentialUserEntity
    {
        $user = User::firstWhere('EmailAddress', $username);

        if (! $user) {
            return null;
        }

        return $this->createUserEntity($user);
    }

    public function findWebauthnUserByUserHandle($userHandle): ?PublicKeyCredentialUserEntity
    {
        $user = User::firstWhere('UserID', $userHandle);

        if (! $user) {
            return null;
        }

        return $this->createUserEntity($user);
    }

    private function createUserEntity($user): PublicKeyCredentialEntity
    {
        return new PublicKeyCredentialUserEntity(
            $user->EmailAddress,
            $user->UserID,
            $user->Forename.' '.$user->Surname,
        );
    }
}
