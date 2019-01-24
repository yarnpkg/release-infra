<?php
class Config {
  // GitHub organization name, repository name, and Git branch.
  // Only builds from this branch in this repo will be archived.
  const ORG_NAME = 'yarnpkg';
  const REPO_NAME = 'yarn';
  const BRANCH = 'master';
  const RELEASE_TAG_FORMAT = '/v[0-9]+(\.[0-9]+)*/';

  // GitHub organization and repository used for releases. This is normally the
  // same as above, but can be changed for debugging purposes
  //const RELEASE_ORG_NAME = 'Daniel15Test';
  const RELEASE_ORG_NAME = Config::ORG_NAME;
  const RELEASE_REPO_NAME = Config::REPO_NAME;

  // Auth token for sign_releases endpoint
  const SIGN_AUTH_TOKEN = 'CHANGEME';
  // File types that should be GPG signed as part of GitHub releases
  const SIGN_FILE_TYPES = '/\.(tar\.gz|js)$/';

  const GITHUB_TOKEN = 'CHANGEME';
  const GITHUB_AUTH_CLIENT_ID = 'CHANGEME';
  const GITHUB_AUTH_CLIENT_SECRET = 'CHANGEME';

  // GitHub usernames of people that are allowed to manage releases
  const MANAGE_ALLOWED_USERS = [
    'arcanis',
    'bestander',
    'daniel15',
    'kittens',
    'byk',
  ];

  const CIRCLECI_TOKEN = 'CHANGEME';

  const APPVEYOR_USERNAME = 'kittens';
  const APPVEYOR_PROJECT_SLUG = 'yarn';
  const APPVEYOR_WEBHOOK_AUTH_TOKEN = 'Bearer CHANGEME';

  const JENKINS_URL = 'https://build.dan.cx/';
  const JENKINS_VERSION_JOB = 'yarn-version';
  const JENKINS_VERSION_TOKEN = 'CHANGEME';

  const NPM_TOKEN = 'CHANGEME';

  const ARTIFACT_PATH = __DIR__.'/../nightly/artifacts/';
  const LOG_PATH = __DIR__.'/../logs/';
  const DEBIAN_INCOMING_PATH = __DIR__.'/../nightly/deb-incoming/';

  const GPG_NIGHTLY = '4F77679369475BAA';
  const GPG_RELEASE = '23E7166788B63E1E';

  // URL to the SecureSign service for signing release files
  //const SECURESIGN_URL = 'http://localhost:18498/';
  const SECURESIGN_URL = 'https://codesigning.internal.d.sb/';
  const SECURESIGN_ACCESS_TOKEN = 'CHANGEME';

  // Auth token for /metrics API
  const METRICS_AUTH_TOKEN = 'Bearer CHANGEME';
}
