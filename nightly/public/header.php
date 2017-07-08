<?php
/**
 * Header for directory listings
 */

require(__DIR__.'/../../vendor/autoload.php');
PageLayout::renderHeader(
  'Nightly Builds',
  'Nightly Builds: '.$_SERVER['REQUEST_URI']
);
?>
