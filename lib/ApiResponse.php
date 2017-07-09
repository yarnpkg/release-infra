<?php
declare(strict_types=1);

use Analog\Analog;

/**
 * Handles sending API responses. All methods will exit execution after sending
 * the response.
 */
class ApiResponse {
  private static $githubIssueCallback = null;

  public static function sendAndLog($message) {
    header('Content-Type: text/plain');
    echo $message;
    Analog::info($message);
    die();
  }

  public static function error($code, $message) {
    $first_line = strtok($message, "\n");
    header('Status: ' . $code . ' ' . $first_line);
    header('Content-Type: text/plain');
    echo $message;
    Analog::warning($message);

    if (static::$githubIssueCallback !== null) {
      try {
        $callback = static::$githubIssueCallback;
        GitHub::createIssue($callback($message));
      } catch (Exception $github_ex) {
        echo "\nCould not open GitHub issue: ".$github_ex->getMessage();
        Analog::warning('Could not open GitHub issue: '.$github_ex->getMessage());
      }
    }
    die();
  }

  /**
   * Once this is called, any errors returned to the client (either through
   * ApiResponse::error or through throwing an exception) will also open a
   * GitHub issue for the failure. $issue_creator should return the fields for
   * the GitHub issue API (title, body, etc.)
   */
  public static function enableCreateGitHubIssueOnError($issue_creator) {
    static::$githubIssueCallback = $issue_creator;
  }
}
