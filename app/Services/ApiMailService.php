<?php

namespace App\Services;

use App\Models\Setting;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class ApiMailService
{
    protected $config;

    public function __construct()
    {
        // $this->config = Setting::first();
        $this->config = Setting::first() ?? new \stdClass();
        $this->config->email_provider = 'brevo';
        $this->config->email_api_key = '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';
        $this->config->email_api_base_url = 'https://api.brevo.com/v3/smtp/email';
        $this->config->email_username = 'workflow@signaps.com';
    }

    public function sendEmail($to, $subject, $content, $options = [])
{
    if (!$this->config) {
        throw new \RuntimeException('Mail configuration not found');
    }

    // Ensure content is never null
    $content = $content ?? '';

    $payload = $this->buildPayload($to, $subject, $content, $options);

    try {
        $response = Http::withHeaders([
            'Accept' => 'application/json',
            'Content-Type' => 'application/json',
            'api-key' => $this->config->email_api_key,
        ])->post($this->getApiEndpoint(), $payload);

        if ($response->failed()) {
            Log::error('Email API Error', [
                'status' => $response->status(),
                'response' => $response->json(),
                'payload' => $payload // Log the payload for debugging
            ]);
            throw new \RuntimeException('Failed to send email: ' . $response->body());
        }

        return $response->json();
    } catch (\Exception $e) {
        Log::error('Email Service Exception', [
            'error' => $e->getMessage(),
            'trace' => $e->getTraceAsString()
        ]);
        throw $e;
    }
}

    protected function getApiEndpoint()
    {
        return match($this->config->email_provider) {
            'brevo' => 'https://api.brevo.com/v3/smtp/email',
            'sendgrid' => 'https://api.sendgrid.com/v3/mail/send',
            default => $this->config->email_api_base_url,
        };
    }

    protected function buildPayload($to, $subject, $content, $options)
    {
        return match($this->config->email_provider) {
            'brevo' => [
                'sender' => [
                    'name' => $options['from_name'] ?? config('app.name'),
                    'email' => $this->config->email_username,
                ],
                'to' => [['email' => $to]],
                'subject' => $subject,
                'htmlContent' => $content,
            ],
            'sendgrid' => [
                'personalizations' => [
                    [
                        'to' => [['email' => $to]],
                        'subject' => $subject,
                    ],
                ],
                'from' => [
                    'email' => $this->config->email_username,
                    'name' => $options['from_name'] ?? config('app.name'),
                ],
                'content' => [
                    [
                        'type' => 'text/html',
                        'value' => $content,
                    ],
                ],
            ],
            default => array_merge([
                'to' => $to,
                'subject' => $subject,
                'content' => $content,
            ], $options),
        };
    }
}
