<?php

/**
 * Class for checking if a supplied password is pwned
 */
class CheckPwned
{

  public static function pwned($password)
  {
    try {

      $http = new GuzzleHttp\Client([
        'timeout'  => 1.0,
      ]);

      $hash = hash('sha1', $password);
      $hashStart = mb_strtoupper(mb_substr($hash, 0, 5));
      $hashEnd = mb_strtoupper(mb_substr($hash, 5));

      $response = $http->request('GET', 'https://api.pwnedpasswords.com/range/' . $hashStart);

      // Check 200 OK
      if ($response->getStatusCode() != 200) {
        throw new Exception('Could not get hash list');
      }

      // Get body
      $body = $response->getBody();
      $hashes = explode("\r\n", $body);

      foreach ($hashes as $line) {
        $lineData = explode(":", $line);

        if ($lineData[0] == $hashEnd) {
          return true;
        }
      }
    } catch (\Exception $e) {
      // Ignore it
    }

    return false;
  }
}
