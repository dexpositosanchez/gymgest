<?php

namespace App\Mail;

use GuzzleHttp\Client;

class ResendMailer
{
    private Client $client;
    private string $apiKey;
    private string $from;

    public function __construct()
    {
        $this->apiKey = config('services.resend.api_key');
        $this->from = config('mail.from.address');
        $this->client = new Client([
            'base_uri' => 'https://api.resend.com',
            'headers' => [
                'Authorization' => 'Bearer ' . $this->apiKey,
                'Content-Type' => 'application/json',
            ],
        ]);
    }

    public function send(string $to, string $subject, string $html): void
    {
        $this->client->post('/emails', [
            'json' => [
                'from' => $this->from,
                'to' => [$to],
                'subject' => $subject,
                'html' => $html,
            ],
        ]);
    }
}