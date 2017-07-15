<?php
/**
 * Determines if a given version number would be a stable or RC release, based
 * on the current stable version number.
 */

require(__DIR__.'/../../nightly/bootstrap.php');

echo Version::isStableVersionNumber($_GET['version']) ? 'stable' : 'rc';
