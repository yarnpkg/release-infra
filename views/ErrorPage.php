<?php

class ErrorPage {
  public static function render(string $message) {
    PageLayout::renderHeader('Error');
    echo '<div class="alert alert-danger" role="alert">', $message, '</div>';
    PageLayout::renderFooter();
    die();
  }
}
