<?php
declare(strict_types=1);

/**
 * Handles GPG signing
 */
class GPG {
  public static function sign(string $filename, string $key): string {
    return static::exec('-u '.escapeshellarg($key).' --armor --output - --detach-sign '.escapeshellarg($filename));
  }

  /**
   * Gets information about the specified subkey.
   */
  public static function getKeyInfo(string $key): ?array {
    $result = static::exec('--with-colons --list-key '.escapeshellarg($key));
    $result = explode("\n", $result);
    foreach ($result as $raw_line) {
      // Format is documented here:
      // https://git.gnupg.org/cgi-bin/gitweb.cgi?p=gnupg.git;a=blob_plain;f=doc/DETAILS
      $line = explode(':', $raw_line);
      if ($line[0] === 'sub' && $line[4] === $key) {
        return [
          'creation_date' => (int)$line[5],
          'expiry_date' => (int)$line[6],
        ];
      }
    }
    return null;
  }

  private static function exec(string $arguments): string {
    exec('gpg '.$arguments.' 2>&1', $output, $ret);
    $output = implode("\n", $output);
    if ($ret !== 0) {
      throw new GPGException($ret.': '.$output);
    }
    return $output;
  }
}

class GPGException extends Exception { }
