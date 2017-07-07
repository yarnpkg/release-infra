<?php
// Handles callbacks from GitHub authentication

error_reporting(E_ALL);
require(__DIR__.'/../../vendor/autoload.php');

use GuzzleHttp\Client;

// Ensure state matches
if (GitHubAuth::getState() !== $_GET['state']) {
  ErrorPage::render('Error: Invalid state');
}

$client = new Client();
$response = $client->post('https://github.com/login/oauth/access_token', [
  'form_params' => [
    'client_id' => Config::GITHUB_AUTH_CLIENT_ID,
    'client_secret' => Config::GITHUB_AUTH_CLIENT_SECRET,
    'code' => $_GET['code'],
    'state' => GitHubAuth::getState(),
  ],
  'headers' => [
    'Accept' => 'application/json',
  ],
]);
$response = json_decode((string)$response->getBody());

if (!empty($response->error_description)) {
  ErrorPage::render('Error: '.htmlspecialchars($response->error_description));
}

if (empty($response->access_token)) {
  ErrorPage::render('Error: No access token!');
}

// Verify that the user is in our whitelist
$user = $client->get('https://api.github.com/user', [
  'headers' => [
    'Authorization' => 'token '.$response->access_token,
  ],
]);
$user = json_decode((string)$user->getBody());

if (!in_array(strtolower($user->login), Config::MANAGE_ALLOWED_USERS)) {
  ErrorPage::render('Sorry, "'.htmlspecialchars($user->login).'" is not allowed to access this page. Contact Daniel15 if you think you should be allowed here!');
}

GitHubAuth::completeLogin($response->access_token, $user);
header('Location: '.$_GET['return']);
