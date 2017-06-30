<?php
declare(strict_types=1);

session_start();

/**
 * Wrapper for GitHub's authentication API.
 */
class GitHubAuth {
  const SESSION_TOKEN = 'github_token';
  const SESSION_STATE = 'github_state';
  const SESSION_USER = 'github_user';

  /**
   * Enforce that the user is logged in and is a maintainer of Yarn.
   */
  public static function enforce() {
    if (static::isLoggedIn()) {
      return;
    }

    $redirect_uri = 'https://'.$_SERVER['HTTP_HOST'].'/manage/login?'.http_build_query([
      'return' => $_SERVER['REQUEST_URI'],
    ], '', '&');

    $_SESSION[static::SESSION_STATE] = bin2hex(random_bytes(32));
    $login_url = 'https://github.com/login/oauth/authorize?'.http_build_query([
      'allow_signup' => 'false',
      'client_id' => Config::GITHUB_AUTH_CLIENT_ID,
      'redirect_uri' => $redirect_uri,
      'scope' => 'user',
      'state' => $_SESSION[static::SESSION_STATE],
    ], '', '&');

    header('Location: ' . $login_url);
    die();
  }

  public static function isLoggedIn(): bool {
    return isset($_SESSION[static::SESSION_TOKEN]);
  }

  public static function getState() {
    return $_SESSION[static::SESSION_STATE];
  }

  public static function getUser() {
    return $_SESSION[static::SESSION_USER];
  }

  public static function completeLogin(string $token, $user) {
    $_SESSION[static::SESSION_TOKEN] = $token;
    $_SESSION[static::SESSION_USER] = $user;
  }
}
