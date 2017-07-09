<?php
/**
 * A CircleCI webhook that pulls build artifacts from AppVeyor and deploys them
 * to the GitHub release.
 */

require(__DIR__.'/../bootstrap.php');
set_time_limit(100);

use GuzzleHttp\Promise;

$build = CircleCI::getAndValidateBuildFromPayload();
// Only publish tagged releases
if (!preg_match(Config::RELEASE_TAG_FORMAT, $build->vcs_tag)) {
  ApiResponse::sendAndLog(sprintf(
    '[%s] Not publishing as release; this is not a release tag. branch=%s tag=%s',
    $build->build_num,
    $build->branch ?? '[none]',
    $build->vcs_tag ?? '[none]'
  ));
}

$artifacts = CircleCI::getArtifactsForBuild($build->build_num);
$tempdir = Filesystem::createTempDir('yarn-release');
ArtifactArchiver::downloadArtifacts($artifacts, $tempdir);

$version = ltrim($build->vcs_tag, 'v');
$is_stable = Version::isStableVersionNumber($version);
$release = GitHub::getOrCreateRelease($build->vcs_tag, $is_stable);
$output = '['.$build->build_num.'] Uploaded to '.$build->vcs_tag.":\n";

$promises = [];
foreach ($artifacts as $filename => $_) {
  $path = $tempdir.$filename;
  $promises[$filename] = GitHub::uploadReleaseArtifact($release, $filename, $path);

  // GPG sign all the files that need to be signed
  if (preg_match(Config::SIGN_FILE_TYPES, $filename)) {
    file_put_contents($path.'.asc', GPG::sign($path, Config::GPG_RELEASE));
    $promises[$filename.'.asc'] = GitHub::uploadReleaseArtifact($release, $filename.'.asc', $path.'.asc');
  }
}

$responses = Promise\unwrap($promises);

$successful = '';
$failed = '';
foreach ($responses as $filename => $succeeded) {
  if ($succeeded) {
    $successful.= $filename."\n";
  } else {
    $failed .= $filename."\n";
  }
}
if ($successful !== '') {
  $output .= $successful;
}
if ($failed !== '') {
  $output .= "\nAlready existed:\n".$failed;
}

$output .= "\n".Release::performPostReleaseJobsIfReleaseIsComplete($version);

ApiResponse::sendAndLog($output);
