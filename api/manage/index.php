<?php
error_reporting(E_ALL);
require(__DIR__.'/../../vendor/autoload.php');

GitHubAuth::enforce();

$heading = 'Release Management';
require(__DIR__.'/../header.php');
?>
Hello World!

<h2>Log Files</h2>
<ul>
<?php
$dir = new GlobIterator(Config::LOG_PATH.'*.log');
foreach ($dir as $file) {
  $name = basename($file->getFileName(), '.log');
  ?>
    <li>
      <a href="/manage/log/<?= $name ?>"><?= $name ?></a>
    </li>
  <?php
}
?>
</ul>
<?php

require(__DIR__.'/../footer.php');
