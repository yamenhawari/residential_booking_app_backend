<?php

namespace App\Services;

use Google\Client;
use Illuminate\Support\Facades\Http;

class FCMService
{
    protected $client;
    protected $projectId;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->setAuthConfig(storage_path('app/firebase_credentials.json'));
        $this->client->addScope('https://www.googleapis.com/auth/firebase.messaging');

        $credentials = json_decode(file_get_contents(storage_path('app/firebase_credentials.json')), true);
        $this->projectId = $credentials['project_id'];
    }

    private function getAccessToken()
    {
        $token = $this->client->fetchAccessTokenWithAssertion();
        return $token['access_token'];
    }

    public function send($token, $title, $body)
    {
        if (!$token) return;

        try {
            $accessToken = $this->getAccessToken();

            $payload = [
                'message' => [
                    'token' => $token,
                    'notification' => [
                        'title' => $title,
                        'body'  => $body,
                    ],
                    'data' => [
                        'click_action' => 'FLUTTER_NOTIFICATION_CLICK',
                        'title' => $title, // Duplicate for data-only handling
                        'body'  => $body,
                    ],
                ],
            ];

            Http::withHeaders([
                'Authorization' => 'Bearer ' . $accessToken,
                'Content-Type'  => 'application/json',
            ])->post("https://fcm.googleapis.com/v1/projects/{$this->projectId}/messages:send", $payload);
        } catch (\Exception $e) {
            //skip
        }
    }
}
