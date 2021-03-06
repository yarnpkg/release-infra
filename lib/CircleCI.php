<?php
declare(strict_types=1);

use GuzzleHttp\Client;

/**
 * Wrapper for CircleCI's API, and utility methods for CircleCI webhooks.
 *
 * NOTE: Unfortunately, CircleCI does not provide any way of authenticating
 * webhook calls (such as a secret authentication token), which is why there is
 * no method to authenticate the authentication token. Instead, we need to hit
 * their API to verify that the build is legit.
 */
class CircleCI {
  /**
   * Gets the build provided in the webhook payload, and verifies that it's a
   * legit CircleCI build rather than someone maliciously calling the webhook.
   */
  public static function getAndValidateBuildFromPayload() {
    $payload = json_decode(file_get_contents('php://input'));
    if (empty($payload)) {
      ApiResponse::error('400', 'No payload provided');
    }
    return static::getAndValidateBuild($payload->payload);
  }

  /**
   * Loads the specified build from the CircleCI API And verifies that it's a
   * legit CircleCI build rather than someone maliciously calling the webhook.
   */
  public static function getAndValidateBuild($build) {
    // First validate the data that was passed in as the payload. If the branch
    // or repo name is not valid, we can quit early (before calling CircleCI's
    // API)
    static::validateBuild($build);

    // Now, load this build from their API and revalidate it, to ensure it's
    // legit and the client isn't tricking us.
    $verified_build = static::getBuild($build->build_num);
    static::validateBuild($verified_build);
    return $verified_build;
  }

  /**
   * Loads a CircleCI build by build number.
   */
  public static function getBuild($build_num) {
    return static::call(
      'project/github/%s/%s/%s',
      Config::ORG_NAME,
      Config::REPO_NAME,
      $build_num
    );
  }

  /**
   * Verifies that the specified CircleCI build is one we care about.
   */
  public static function validateBuild($build) {
    if (
      (
        $build->branch !== Config::BRANCH &&
        !preg_match(Config::RELEASE_TAG_FORMAT, $build->vcs_tag ?? '')
      ) ||
      $build->username !== Config::ORG_NAME ||
      $build->reponame !== Config::REPO_NAME
    ) {
      ApiResponse::sendAndLog(sprintf(
        'Not archiving; this build is not on the correct branch: %s/%s/%s',
        $build->username,
        $build->reponame,
        $build->branch
      ));
    }

    $build_num = $build->build_num;
    if (empty($build_num)) {
      ApiResponse::error('400', 'No build number found');
    }
  }

  public static function getArtifactsForBuild($build_num) {
    $artifacts = static::call(
      'project/github/%s/%s/%s/artifacts',
      Config::ORG_NAME,
      Config::REPO_NAME,
      $build_num
    );
    $urls = [];
    foreach ($artifacts as $artifact) {
      $filename = basename($artifact->path);
      $urls[$filename] = $artifact->url;
    }
    return $urls;
  }

  /**
   * Calls the CircleCI API.
   */
  public static function call(string $uri, ...$uri_args) {
    $client = new Client([
      'base_uri' => 'https://circleci.com/api/v1.1/',
    ]);
    $response = $client->get(vsprintf($uri, $uri_args), [
      'headers' => [
        'Accept' => 'application/json',
      ],
    ]);
    return json_decode((string)$response->getBody());
  }
}
