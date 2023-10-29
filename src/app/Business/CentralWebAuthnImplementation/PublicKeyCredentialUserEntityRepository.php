<?php

namespace App\Business\CentralWebAuthnImplementation;

use App\Models\Central\User;
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
        /** @var User $user */
        $user = User::find($userHandle);

        if (! $user) {
            return null;
        }

        return $this->createUserEntity($user);
    }

    private function createUserEntity($user): PublicKeyCredentialEntity
    {
        return new PublicKeyCredentialUserEntity(
            $user->email,
            $user->id,
            $user->first_name.' '.$user->last_name,
        );
    }
}
