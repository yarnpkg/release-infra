<?php
/**
 * A CircleCI webhook that pulls build artifacts from AppVeyor and deploys them
 * to the GitHub release.
 */

require(__DIR__.'/../bootstrap.php');
set_time_limit(100);

use GuzzleHttp\Promise;

$build = CircleCI::getAndValidateBuildFromPayload();

ApiResponse::enableCreateGitHubIssueOnError(function ($message) use ($build) {
  $release_host = str_replace('nightly', 'release', $_SERVER['HTTP_HOST']);
  $body = <<<EOT
An error was encountered while processing the CircleCI release build of {$build->vcs_tag}:

```
{$message}
```

Re-running the build on CircleCI might fix it. [Click "Rebuild" on this page to trigger a rebuild]({$build->build_url})

Full logs: https://{$release_host}/log/release_circleci

cc @Daniel15 @{$build->user->login}
EOT;
  return [
    'title' => 'Error releasing '.$build->vcs_tag,
    'body' => $body,
    'labels' => ['bug-high-priority', 'bug-distrib'],
    'assignees' => [$build->user->login],
  ];
});

// Only publish tagged releases
if (!preg_match(Config::RELEASE_TAG_FORMAT, $build->vcs_tag)) {
  ApiResponse::sendAndLog(sprintf(
    '[%s] Not publishing as release; this is not a release tag. branch=%s tag=%s',
    $build->build_num,
    $build->branch ?? '[none]',
    $build->vcs_tag ?? '[none]'
  ));
}

if ($build->status !== 'success' && $build->status !== 'fixed') {
  ApiResponse::error('400', sprintf(
    'Build #%s in wrong status (%s), expected "success". Not releasing it.',
    $build->build_num,
    $build->status
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
