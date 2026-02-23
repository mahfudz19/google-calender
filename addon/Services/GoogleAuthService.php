<?php

namespace Addon\Services;

use Google\Client;
use Google\Service\Oauth2;
use Exception;

class GoogleAuthService
{
  private Client $client;

  public function __construct()
  {
    $this->client = new Client();

    $clientId = env('GOOGLE_CLIENT_ID');
    $clientSecret = env('GOOGLE_CLIENT_SECRET');
    $redirectUri = env('GOOGLE_REDIRECT_URI');

    if (!$clientId || !$clientSecret) {
      throw new Exception("Google OAuth Credentials not set in .env");
    }

    $this->client->setClientId($clientId);
    $this->client->setClientSecret($clientSecret);
    if ($redirectUri) {
      $this->client->setRedirectUri($redirectUri);
    }

    $this->client->addScope('email');
    $this->client->addScope('profile');
  }

  public function getAuthUrl(): string
  {
    return $this->client->createAuthUrl();
  }

  public function handleCallback(string $code): array
  {
    $token = $this->client->fetchAccessTokenWithAuthCode($code);
    if (isset($token['error'])) {
      throw new Exception('Error fetching token: ' . $token['error']);
    }

    $this->client->setAccessToken($token);

    $oauth2 = new Oauth2($this->client);
    $userInfo = $oauth2->userinfo->get();

    $email = $userInfo->email;
    $allowedDomain = env('GOOGLE_ALLOWED_DOMAIN', '@inbitef.ac.id');

    if ($allowedDomain && !str_ends_with($email, $allowedDomain)) {
      throw new Exception('Akses Ditolak: Hanya email ' . $allowedDomain . ' yang diizinkan.');
    }

    return [
      'email' => $email,
      'name' => $userInfo->name,
      'picture' => $userInfo->picture,
      'google_id' => $userInfo->id,
      'token' => $token,
    ];
  }
}
