<?php
error_reporting(E_ALL);
require(__DIR__.'/../../vendor/autoload.php');

GitHubAuth::enforce();
$latest_stable = Version::getLatestStableYarnVersion();
$latest_rc = Version::getLatestRCYarnVersion();
$show_promote_rc = version_compare($latest_stable, $latest_rc, '<');

PageLayout::renderHeader('Release Management');
?>
Hello World!

<?php if ($show_promote_rc) { ?>
  <h2>Release Management</h2>
  <form action="/promote_rc" method="post" onsubmit="return confirm('Are you sure you want to promote this version to stable?')">
    <button class="btn btn-primary">
      Promote RC (<?= $latest_rc ?>) to stable
    </button>
  </form>
<?php } ?>

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

<a data-toggle="collapse" href="#advanced" aria-expanded="false" aria-controls="advanced">
  Show advanced debug utils
</a>

<div id="advanced" class="collapse">
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
</div>

<?php
PageLayout::renderFooter();
