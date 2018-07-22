<?php
/**
 * @author      Christopher Heppell <chris.heppell@chesterlestreetasc.co.uk>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/Chester-le-Street-ASC/Membership
 */
namespace ChesterLeStreet\OAuth2\Repositories;
use League\OAuth2\Server\Entities\RefreshTokenEntityInterface;
use League\OAuth2\Server\Repositories\RefreshTokenRepositoryInterface;
use ChesterLeStreet\OAuth2\Entities\RefreshTokenEntity;
class RefreshTokenRepository implements RefreshTokenRepositoryInterface
{
  /**
   * {@inheritdoc}
   */
  public function persistNewRefreshToken(RefreshTokenEntityInterface $refreshTokenEntityInterface)
  {
    // Some logic to persist the refresh token in a database
  }
  /**
   * {@inheritdoc}
   */
  public function revokeRefreshToken($tokenId)
  {
    // Some logic to revoke the refresh token in a database
  }
  /**
   * {@inheritdoc}
   */
  public function isRefreshTokenRevoked($tokenId)
  {
    return false; // The refresh token has not been revoked
  }
  /**
   * {@inheritdoc}
   */
  public function getNewRefreshToken()
  {
    return new RefreshTokenEntity();
  }
}
