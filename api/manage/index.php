<?php
error_reporting(E_ALL);
require(__DIR__.'/../../vendor/autoload.php');

GitHubAuth::enforce();

$heading = 'Release Management';
require(__DIR__.'/../header.php');
?>
Hello World!
<?php
require(__DIR__.'/../footer.php');
