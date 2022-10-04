<?php

namespace WebAuthnImplementation;

use Webauthn\PublicKeyCredentialSourceRepository as PublicKeyCredentialSourceRepositoryInterface;
use Webauthn\PublicKeyCredentialSource;
use Webauthn\PublicKeyCredentialUserEntity;

class PublicKeyCredentialSourceRepository implements PublicKeyCredentialSourceRepositoryInterface
{
  public function __construct()
  {
  }

  public function findOneByCredentialId(string $publicKeyCredentialId): ?PublicKeyCredentialSource
  {
    $db = app()->db;

    $find = $db->prepare("SELECT `credential` FROM `userCredentials` WHERE `credential_id` = ?");
    $find->execute([
      base64_encode($publicKeyCredentialId),
    ]);

    $credential = $find->fetchColumn();

    if (!$credential) return null;

    return PublicKeyCredentialSource::createFromArray(json_decode($credential, true));
  }

  /**
   * @return PublicKeyCredentialSource[]
   */
  public function findAllForUserEntity(PublicKeyCredentialUserEntity $publicKeyCredentialUserEntity): array
  {
    $db = app()->db;

    $sources = [];

    $find = $db->prepare("SELECT `credential` FROM `userCredentials` WHERE `user_id` = ?");
    $find->execute([
      $publicKeyCredentialUserEntity->getId(),
    ]);

    while ($data = $find->fetchColumn()) {
      $source = PublicKeyCredentialSource::createFromArray(json_decode($data, true));
      $sources[] = $source;
    }
    return $sources;
  }

  public function saveCredentialSource(PublicKeyCredentialSource $publicKeyCredentialSource): void
  {
    $db = app()->db;

    // Check for existing!

    $getCount = $db->prepare("SELECT COUNT(*) FROM `userCredentials` WHERE `credential_id` = ?");
    $getCount->execute([
      base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId()),
    ]);

    $existing = $getCount->fetchColumn() > 0;

    if ($existing) {
      $update = $db->prepare("UPDATE `userCredentials` SET `credential` = ? WHERE `credential_id` = ?");
      $update->execute([
        json_encode($publicKeyCredentialSource->jsonSerialize()),
        base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId()),
      ]);
    } else {
      $user = app()->user;
      $userId = $user->getId();

      $insert = $db->prepare("INSERT INTO `userCredentials` (`id`, `credential_id`, `user_id`, `credential`) VALUES (?, ?, ?, ?)");
      $insert->execute([
        \Ramsey\Uuid\Uuid::uuid4(),
        base64_encode($publicKeyCredentialSource->getPublicKeyCredentialId()),
        $userId,
        json_encode($publicKeyCredentialSource->jsonSerialize()),
      ]);
    }
  }
}
