<?php
declare(strict_types=1);

use GuzzleHttp\Client;

/**
 * Wrapper for GitHub's API.
 */
class GitHub {
  public static function call(string $uri, ...$uri_args) {
    $client = new Client([
      'base_uri' => 'https://api.github.com/',
    ]);
    $response = $client->get(vsprintf($uri, $uri_args), [
      'headers' => [
        'Authorization' => 'token '.Config::GITHUB_TOKEN,
      ],
    ]);
    return json_decode((string)$response->getBody());
  }

  public static function post(string $uri, array $uri_args, array $post_data) {
    $client = new Client([
      'base_uri' => 'https://api.github.com/',
    ]);
    $response = $client->post(vsprintf($uri, $uri_args), [
      'headers' => [
        'Authorization' => 'token '.Config::GITHUB_TOKEN,
      ],
      'json' => $post_data,
    ]);
    return json_decode((string)$response->getBody());
  }

  public static function getOrCreateRelease(string $name, bool $is_stable) {
    // Check if this release exists
    try {
      return static::getRelease($name);
    } catch (\GuzzleHttp\Exception\TransferException $e) {
      // Release doesn't exist yet, so create a new one
      return static::createRelease($name, $is_stable);
    }
  }

  public static function getRelease(string $name) {
    return static::call(
      'repos/%s/%s/releases/tags/%s',
      Config::RELEASE_ORG_NAME,
      Config::RELEASE_REPO_NAME,
      $name
    );
  }

  public static function createRelease(string $name, bool $is_stable) {
    return static::post(
      'repos/%s/%s/releases',
      [Config::RELEASE_ORG_NAME, Config::RELEASE_REPO_NAME],
      [
        'prerelease' => !$is_stable,
        'name' => $name,
        'tag_name' => $name,
      ]
    );
  }

  public static function updateRelease(int $id, array $params) {
    return static::post(
      'repos/%s/%s/releases/%s',
      [Config::RELEASE_ORG_NAME, Config::RELEASE_REPO_NAME, $id],
      $params
    );
  }

  /**
   * Upload an artifact to the specific GitHub release. Returns a promise so
   * multiple files can be uploaded in parallel.
   */
  public static function uploadReleaseArtifact(
    $release,
    string $filename,
    string $path
  ): \GuzzleHttp\Promise\PromiseInterface {
    $client = new Client();
    $uri = new \Rize\UriTemplate\UriTemplate();
    $upload_url = $uri->expand(
      $release->upload_url,
      ['name' => $filename]
    );
    return $client->postAsync($upload_url, [
      'body' => fopen($path, 'r'),
      'headers' => [
        'Authorization' => 'token '.Config::GITHUB_TOKEN,
        'Content-Type' => 'application/octet-stream',
      ],
    ])->then(
      function($response) {
        return true;
      },
      function($ex) {
        try {
          if (
            $ex instanceof \GuzzleHttp\Exception\ClientException &&
            $ex->hasResponse()
          ) {
            $response = json_decode((string)$ex->getResponse()->getBody());
            if ($response->errors[0]->code === 'already_exists') {
              // Asset already exists. This is okay, let's treat it as a
              // non-critical issue.
              return false;
            }
          }
        } catch (Exception $inner_ex) {
          // Error occurred while trying to determine the type of error. Just
          // ignore it and let the generic "throw $ex" below handle it.
        }
        throw $ex;
      }
    );
  }

  public static function createIssue($config) {
    return static::post(
      'repos/%s/%s/issues',
      [Config::RELEASE_ORG_NAME, Config::RELEASE_REPO_NAME],
      $config
    );
  }
}
