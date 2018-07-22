<?php
/**
 * @author      Christopher Heppell <chris.heppell@chesterlestreetasc.co.uk>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/Chester-le-Street-ASC/Membership
 */
namespace ChesterLeStreet\OAuth2\Repositories;
use League\OAuth2\Server\Entities\AccessTokenEntityInterface;
use League\OAuth2\Server\Entities\ClientEntityInterface;
use League\OAuth2\Server\Repositories\AccessTokenRepositoryInterface;
use ChesterLeStreet\OAuth2\Entities\AccessTokenEntity;
class AccessTokenRepository implements AccessTokenRepositoryInterface
{
  /**
   * {@inheritdoc}
   */
  public function persistNewAccessToken(AccessTokenEntityInterface $accessTokenEntity)
  {
    // Some logic here to save the access token to a database
  }
  /**
   * {@inheritdoc}
   */
  public function revokeAccessToken($tokenId)
  {
    // Some logic here to revoke the access token
  }
  /**
   * {@inheritdoc}
   */
  public function isAccessTokenRevoked($tokenId)
  {
    return false; // Access token hasn't been revoked
  }
  /**
   * {@inheritdoc}
   */
  public function getNewToken(ClientEntityInterface $clientEntity, array $scopes, $userIdentifier = null)
  {
    $accessToken = new AccessTokenEntity();
    $accessToken->setClient($clientEntity);
    foreach ($scopes as $scope) {
        $accessToken->addScope($scope);
    }
    $accessToken->setUserIdentifier($userIdentifier);
    return $accessToken;
  }
}
