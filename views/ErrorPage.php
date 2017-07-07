<?php

class ErrorPage {
  public static function render(string $message) {
    $heading = 'Error';
    require(__DIR__.'/../api/header.php');
    echo '<div class="alert alert-danger" role="alert">', $message, '</div>';
    require(__DIR__.'/../api/footer.php');
    die();
  }
}
