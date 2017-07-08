<?php
/**
 * Allows viewing of log files
 */

const MAX_LINES = 100;

error_reporting(E_ALL);
require(__DIR__.'/../../vendor/autoload.php');

GitHubAuth::enforce();

$path = Config::LOG_PATH.$_GET['file'].'.log';

if (
  !preg_match('/^[A-Z0-9_\-]+$/i', $_GET['file']) ||
  !file_exists($path)
) {
  ErrorPage::render('Invalid file name');
}

$file = file($path, FILE_IGNORE_NEW_LINES);

PageLayout::renderHeader('Log: '.$_GET['file']);
?>
<p><a href="/">&larr; Back</a></p>
<pre>
<?php
$lines = count($file);
if ($lines > MAX_LINES) {
  echo '[...omitted ', $lines - MAX_LINES, " lines...]\n\n";
  $file = array_slice($file, -MAX_LINES);
}

echo implode("\n", $file);
echo '</pre>';
PageLayout::renderFooter();
