<?php
error_reporting(E_ALL);
require(__DIR__.'/../../vendor/autoload.php');

use GuzzleHttp\Client;

GitHubAuth::enforce();

if (empty($_POST['hook'])) {
  header('Location: /');
  die();
}

PageLayout::renderHeader('Webhook Debugger: '.htmlspecialchars($_POST['hook']));

$headers = array();
$method = 'POST';
$post = null;
$post_type = 'json';
try {
  switch ($_POST['hook']) {
    case 'release_appveyor':
      $tag = 'v'.ltrim($_POST['tag'], 'v');
      $build = AppVeyor::getBuildForTag($tag);

      // The format of builds returned via the API is slightly different to the
      // format taken by webhooks. We need to normaize the format here, before
      // calling the webhook.
      $post = array_merge(
        (array)$build->project,
        (array)$build->build
      );
      $post['environmentVariables'] = array(
        'node_version' => 6,
      );
      $post['jobId'] = $build->build->jobs[0]->jobId;
      $post['buildVersion'] = $build->build->version;
      $headers = array(
        'Authorization' => Config::APPVEYOR_WEBHOOK_AUTH_TOKEN,
      );
      break;

    case 'release_circleci':
      // Allow either CircleCI URL or build number
      $slash_pos = strrpos($_POST['url'], '/');
      $build_num = $slash_pos === false
        ? $_POST['url']
        : substr($_POST['url'], $slash_pos + 1);
      $build = CircleCI::getBuild($build_num);
      $post['payload'] = $build;
      break;

    default:
      throw new Exception('Unknown hook '.htmlspecialchars($_POST['hook']));
  }

  $host = str_replace('release', 'nightly', $_SERVER['HTTP_HOST']);
  $scheme = strpos($host, 'localdev') === false ? 'https' : 'http';
  $url = $scheme.'://'.$host.'/'.$_POST['hook'];

  try {
    $client = new Client();
    $response = $client->request($method, $url, [
      'headers' => $headers,
      'json' => $post_type === 'json' ? $post : null,
    ]);
    echo '<pre>'.htmlspecialchars($response->getBody()).'</pre>';
  } catch (\GuzzleHttp\Exception\RequestException $ex) {
    if (!$ex->hasResponse()) {
      throw $ex;
    }
    echo '<pre>'.htmlspecialchars($ex->getResponse()->getBody()).'</pre>';
  }
} catch (Exception $ex) {
  echo
    '<div class="alert alert-danger" role="alert">
      Could not execute hook "'.htmlspecialchars($_POST['hook']).'": '.$ex->getMessage(), '
    </div>';
}

PageLayout::renderFooter();
