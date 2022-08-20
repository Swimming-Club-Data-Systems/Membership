<?php

namespace App\Business\WebAuthnImplementation;

use App\Models\Tenant\Auth\UserCredential;
use Webauthn\PublicKeyCredentialSourceRepository as PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;
use Illuminate\Support\Facades\Auth;
use App\Models\Tenant\User;

class PublicKeyCredentialSourceRepository implements PublicKeyCredentialSourceRepositoryInterface
{
  public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
  {
    $credential = UserCredential::firstWhere('credential_id', base64_encode($publicKeyCredentialId));

    if (!$credential) {
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
    $credential = UserCredential::firstWhere('credential_id', base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId()));

    if ($credential) {
      // Update
      $credential->credential = json_encode($publicKeyCredentialSource->jsonSerialize());
      $credential->save();
    } else {
      // Insert
      if (!Auth::id()) {
        return;
      }

      $user = User::find(Auth::id());

      $credential = new UserCredential();
      $credential->credential_id = base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId());
      $credential->credential = json_encode($publicKeyCredentialSource->jsonSerialize());

      $user->userCredentials()->save($credential);
    }
  }
}
