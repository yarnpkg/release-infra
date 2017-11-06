<?php
/**
 * An AppVeyor publishing webhook that pulls build artifacts from AppVeyor and
 * deploys them to the GitHub release.
 */

require(__DIR__.'/../bootstrap.php');

use GuzzleHttp\Client;

AppVeyor::validateWebhookAuth();

$payload = json_decode(file_get_contents('php://input'));
if (empty($payload)) {
  ApiResponse::error('400', 'No payload provided');
}

// Ignore the Node.js 4.x build
if ($payload->environmentVariables->node_version === '4') {
  ApiResponse::sendAndLog(sprintf(
    '[#%s] Ignoring Node.js 4.x build',
    $payload->buildVersion
  ));
}

$build = AppVeyor::getAndValidateBuild($payload);

ApiResponse::enableCreateGitHubIssueOnError(function ($message) use ($build) {
  $release_host = str_replace('nightly', 'release', $_SERVER['HTTP_HOST']);
  $body = <<<EOT
An error was encountered while processing the AppVeyor release build of {$build->build->branch}:

```
{$message}
```

Re-running the build on AppVeyor might fix it. [Click "Re-build Commit" on this page to trigger a rebuild](https://ci.appveyor.com/project/kittens/yarn/build/{$build->build->buildNumber})

Full logs: https://{$release_host}/log/release_appveyor

cc @Daniel15 @{$build->build->committerUsername}
EOT;
  return [
    'title' => 'Error releasing '.$build->build->branch,
    'body' => $body,
    'labels' => ['bug-high-priority', 'bug-distrib'],
    'assignees' => [$build->build->committerUsername],
  ];
});

// Ensure provided job ID is part of this build
$job_id = $payload->jobId;
$job = null;
foreach ($build->build->jobs as $current_job) {
  if ($current_job->jobId === $job_id) {
    $job = $current_job;
    break;
  }
}
if ($job === null) {
  ApiResponse::error('400', 'Invalid job ID: '.$job_id);
}
if (isset($payload->passed) && !$payload->passed) {
  ApiResponse::error('400', sprintf(
    '[#%s] Build in wrong status (passed = false), not releasing it',
    $build->build->version
  ));
}

// Get artifacts for this job, and just download the first one
$urls = AppVeyor::getArtifactsForJob($job_id);
$url = current($urls);
$filename = key($urls);

$signed_tempfile = SecureSign::authenticodeSignFromURL($url);

// Get version number from filename, and get the release with this version number
preg_match('/yarn-(?P<version>.+?)(-unsigned)?\.msi/', $filename, $matches);
if (empty($matches)) {
  ApiResponse::error('400', 'Unexpected filename: '.$filename);
}
$version = ltrim($matches['version'], 'v');
$is_stable = Version::isStableVersionNumber($version);
$release = GitHub::getOrCreateRelease('v'.$version, $is_stable);

// Upload the file to the release
$signed_filename = str_replace('-unsigned', '', $filename);
$was_uploaded = GitHub::uploadReleaseArtifact($release, $signed_filename, $signed_tempfile)->wait();
$output = $was_uploaded
  ? 'Published '.$signed_filename.' to '.$version
  : 'File '.$signed_filename.' already existed in '.$version.'!';

$output .= "\n".Release::performPostReleaseJobsIfReleaseIsComplete($version);

ApiResponse::sendAndLog($output);
