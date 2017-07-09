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

<h2>Re-Run Release Webhooks</h2>
<p>
  Use these tools to re-run a webhook if it failed to run for some reason.
  <strong>Be careful!</strong> This will affect <strong>LIVE</strong> releases!
</p>


<form method="post" action="/run_webhook" class="form-inline">
  <label for="appveyor_tag">AppVeyor tag:</label>
  <input type="text" name="tag" id="appveyor_tag" placeholder="eg. v0.27.5" class="form-control" />
  <input type="hidden" name="hook" value="release_appveyor" />
  <button type="submit" class="btn btn-primary">Run</button>
</form><br />

<form method="post" action="/run_webhook" class="form-inline">
  <label for="circleci_url">CircleCI build number:</label>
  <input type="text" name="url" id="circleci_url" placeholder="eg. 4294" class="form-control" />
  <input type="hidden" name="hook" value="release_circleci" />
  <button type="submit" class="btn btn-primary">Run</button><br />
  Note that this needs to be the build number of the <strong>release tag</strong> CircleCI build (eg. <a href="https://circleci.com/gh/yarnpkg/yarn/4294">4294</a>)
</form>

<?php
PageLayout::renderFooter();
