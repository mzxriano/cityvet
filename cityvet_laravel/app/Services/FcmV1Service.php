<?php

namespace App\Services;

use Google\Auth\OAuth2;
use Illuminate\Support\Facades\Http;

class FcmV1Service
{
    protected $projectId;
    protected $credentialsPath;

    public function __construct()
    {
        $this->projectId = env('FCM_PROJECT_ID');
        $this->credentialsPath = storage_path('app/private/fcm-service-account.json');
    }

    protected function getAccessToken()
    {
        $json = file_get_contents($this->credentialsPath);
        \Log::info('FCM JSON raw', ['json' => $json]);
        $jsonKey = json_decode($json, true);
        \Log::info('FCM JSON decoded', $jsonKey);

        $oauth = new OAuth2([
            'audience' => 'https://oauth2.googleapis.com/token',
            'issuer' => $jsonKey['client_email'],
            'signingAlgorithm' => 'RS256',
            'signingKey' => $jsonKey['private_key'],
            'scope' => 'https://www.googleapis.com/auth/firebase.messaging',
            'tokenCredentialUri' => $jsonKey['token_uri'],
        ]);

        $token = $oauth->fetchAuthToken();
        return $token['access_token'];
    }

    public function send($deviceToken, $title, $body, $data = [])
    {
        $accessToken = $this->getAccessToken();

        $url = "https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send";

        $payload = [
            'message' => [
                'token' => $deviceToken,
                'notification' => [
                    'title' => $title,
                    'body' => $body,
                ],
                'data' => $data,
            ],
        ];

        $response = Http::withToken($accessToken)
            ->post($url, $payload);

        return $response->json();
    }
} 