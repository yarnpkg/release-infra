<?php
declare(strict_types=1);

use GuzzleHttp\Client;

/**
 * Wrapper for npm's API.
 */
class NPM {
  public static function addDistTag(string $package, string $version, string $tag) {
    $client = new Client([
      'base_uri' => 'https://registry.npmjs.org/',
    ]);
    $uri = vsprintf(
      '/-/package/%s/dist-tags/%s',
      [urlencode($package), $tag]
    );
    $response = $client->put($uri, [
      'headers' => [
        'Authorization' => 'Bearer '.Config::NPM_TOKEN,
        'Content-Type' => 'text/plain',
      ],
      'body' => $version,
    ]);
    return json_decode((string)$response->getBody());
  }
}
