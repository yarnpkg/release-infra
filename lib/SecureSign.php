<?php
declare(strict_types=1);

use GuzzleHttp\Client;

/**
 * Handles calling SecureSign service to sign release files.
 * https://github.com/Daniel15/SecureSign
 */
class SecureSign {
  public static function authenticodeSignFromURL(string $url) {
    $tempfile = tempnam(sys_get_temp_dir(), 'yarn-artifact');

    $api_url = Config::SECURESIGN_URL.'sign/authenticode';
    $client = new Client();
    $client->post($api_url, [
      'form_params' => [
        'accessToken' => Config::SECURESIGN_ACCESS_TOKEN,
        'artifactUrl' => $url,
      ],
      'sink' => $tempfile,
    ]);
    return $tempfile;
  }
}
