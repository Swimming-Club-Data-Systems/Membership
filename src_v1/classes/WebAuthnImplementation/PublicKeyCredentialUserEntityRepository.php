<?php

namespace WebAuthnImplementation;

use Webauthn\PublicKeyCredentialEntity;
use Webauthn\PublicKeyCredentialSourceRepository as PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

final class PublicKeyCredentialUserEntityRepository
{
  public function __construct() {
  }

  public function findWebauthnUserByUsername(string $username): ?PublicKeyCredentialUserEntity {
    $db = app()->db;
    $tenant = app()->tenant;

    $getUser = $db->prepare("SELECT EmailAddress, Forename, Surname, UserID FROM users WHERE EmailAddress = ? AND Tenant = ? AND Active");
    $getUser->execute([
      $username,
      $tenant->getId(),
    ]);
    $user = $getUser->fetch(\PDO::FETCH_ASSOC);

    if (!$user) return null;

    return $this->createUserEntity($user);
  }

  public function findWebauthnUserByUserHandle($userHandle): ?PublicKeyCredentialUserEntity {
    $db = app()->db;
    $tenant = app()->tenant;

    $getUser = $db->prepare("SELECT EmailAddress, Forename, Surname, UserID FROM users WHERE UserID = ? AND Tenant = ? AND Active");
    $getUser->execute([
      $userHandle,
      $tenant->getId(),
    ]);
    $user = $getUser->fetch(\PDO::FETCH_ASSOC);

    if (!$user) return null;

    return $this->createUserEntity($user);
  }

  private function createUserEntity($user): PublicKeyCredentialEntity {
    return new PublicKeyCredentialUserEntity(
      $user['EmailAddress'],
      $user['UserID'],
      $user['Forename'] . ' ' . $user['Surname']
    );
  }
}
