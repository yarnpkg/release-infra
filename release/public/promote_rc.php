<?php
/**
 * Promoted the current release candidate version to stable
 */

error_reporting(E_ALL);
require(__DIR__.'/../../vendor/autoload.php');

GitHubAuth::enforce();

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
  header('Location: /');
  die();
}

$version = Version::getLatestRCYarnVersion();
PageLayout::renderHeader('Promote RC: '.$version);

// Mark GitHub release as stable
$release = GitHub::getRelease('v'.$version);
GitHub::updateRelease($release->id, [
  'prerelease' => false,
]);

// Mark npm release as stable
NPM::addDistTag('yarn', $version, 'latest');

// Perform post-release magic (update version number on site, publish to
// Chocolatey, publish to Homebrew, etc)
echo Release::performPostReleaseJobsIfReleaseIsComplete($version);

echo '<hr />Done!';

PageLayout::renderFooter();
