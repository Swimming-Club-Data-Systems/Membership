<?php
/**
 * @author      Christopher Heppell <chris.heppell@chesterlestreetasc.co.uk>
 * @copyright   Copyright (c) Alex Bilbie
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/Chester-le-Street-ASC/Membership
 */
namespace ChesterLeStreet\OAuth2\Repositories;
use League\OAuth2\Server\Entities\AuthCodeEntityInterface;
use League\OAuth2\Server\Repositories\AuthCodeRepositoryInterface;
use ChesterLeStreet\OAuth2\Entities\AuthCodeEntity;
class AuthCodeRepository implements AuthCodeRepositoryInterface
{
  /**
   * {@inheritdoc}
   */
  public function persistNewAuthCode(AuthCodeEntityInterface $authCodeEntity)
  {
    // Some logic to persist the auth code to a database
  }
  /**
   * {@inheritdoc}
   */
  public function revokeAuthCode($codeId)
  {
    // Some logic to revoke the auth code in a database
  }
  /**
   * {@inheritdoc}
   */
  public function isAuthCodeRevoked($codeId)
  {
    return false; // The auth code has not been revoked
  }
  /**
   * {@inheritdoc}
   */
  public function getNewAuthCode()
  {
    return new AuthCodeEntity();
  }
}
