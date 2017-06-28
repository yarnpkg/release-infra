# Nightly Builds + Release Infrastructure

This repo also contains infrastructure for releasing Yarn, both the stable release as well as nightly builds. This is hosted at https://nightly.yarnpkg.com/

Available endpoints on `nightly.yarnpkg.com`:

* `/archive_appveyor`: Archives all master builds from AppVeyor (https://ci.appveyor.com/project/kittens/yarn) onto the nightly builds site. Called as a webhook from the AppVeyor build
* `/archive_circleci`: Archives all master builds from CircleCI (https://circleci.com/gh/yarnpkg/yarn) onto the nightly builds site. Called as a webhook from the CircleCI build
* `/latest.json`: Contains the version numbers and URLs to all the latest nightly builds
* `/latest.[type]` (eg. `/latest.tar.gz`, `/latest.msi`): Redirects to the latest nightly build of this type
* `/latest-version`: Returns the version number of the latest nightly build
* `/latest-[type]-version` (eg. `/latest-tar-version`, `/latest-msi-version`): Returns the version number of the latest nightly build containing a file of this format. This is useful because the Windows and Linux builds are performed separately, so the version number of the latest MSI may differ from the other version numbers.
* `/[type]-builds` (eg. `/tar-builds`, `/msi-builds`): Returns a list of all the nightly builds available for this type. Used on the nightly builds page (https://yarnpkg.com/en/docs/nightly).
* `/release_appveyor`: Handles stable release builds from AppVeyor. Grabs the MSI from AppVeyor, Authenticode signs it, then uploads it to the GitHub release. Called as a webhook from the AppVeyor build
* `/release_circleci`: Similar to `release_appveyor`, except for CircleCI builds. Called a webhook from the CircleCI build
* `/sign_releases`: GPG signs all `.tar.gz` and `.js` files for all GitHub releases, attaching the signatures as `.asc` files to the GitHub releases

Directories in this repo:
* `nginx`: Nginx configuration for `nightly.yarnpkg.com` and `yarn.fyi`
* `api`: Publicly accessible endpoints for `nightly.yarnpkg.com`
* `lib`: Libraries used by the release site  
  * `config.php`: Contains all configuration for the release infra. Includes API tokens (AppVeyor, CircleCI, GitHub), GPG IDs to use when signing files, and path to the Authenticode key for signing the Windows installer.
