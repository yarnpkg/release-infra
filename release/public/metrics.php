<?php
/**
 * Gets metrics to track in Prometheus, such as the expiry date of the GPG
 * signing key.
 */

declare(strict_types=1);
require(__DIR__.'/../../nightly/bootstrap.php');

use \Prometheus\{CollectorRegistry,RenderTextFormat};
use \Prometheus\Storage\InMemory;

$auth_token = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
if (empty($auth_token) || $auth_token !== Config::METRICS_AUTH_TOKEN) {
  ApiResponse::error('403', 'Unauthorized');
}

function addGPGKeyMetrics(CollectorRegistry $registry, string $type, string $key): void {
  $key_info = GPG::getKeyInfo($key);
  $registry
    ->getOrRegisterGauge('gpg', 'key_expiry', 'Expiration date of GPG key', ['type'])
    ->set($key_info['expiry_date'], [$type]);
}

$registry = new CollectorRegistry(new InMemory());
addGPGKeyMetrics($registry, 'release', Config::GPG_RELEASE);
addGPGKeyMetrics($registry, 'nightly', Config::GPG_NIGHTLY);

$renderer = new RenderTextFormat();
$result = $renderer->render($registry->getMetricFamilySamples());

header('Content-type: ' . RenderTextFormat::MIME_TYPE);
echo $result;
