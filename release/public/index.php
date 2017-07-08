<?php
error_reporting(E_ALL);
require(__DIR__.'/../../vendor/autoload.php');

GitHubAuth::enforce();

PageLayout::renderHeader('Release Management');
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
      <a href="/log/<?= $name ?>"><?= $name ?></a>
    </li>
  <?php
}
?>
</ul>
<?php
PageLayout::renderFooter();
