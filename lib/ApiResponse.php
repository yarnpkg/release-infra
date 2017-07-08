<?php
declare(strict_types=1);

use Analog\Analog;

/**
 * Handles sending API responses. All methods will exit execution after sending
 * the response.
 */
class ApiResponse {
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
    die();
  }
}
