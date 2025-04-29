<?php

namespace App\Services;

use App\Models\Setting;

class MailConfigService {
    public function getMailConfiguration()
    {
        // \Log::info("Application Name: " . $appName);
        // $mailsetting = Setting::first();
        // if ($mailsetting) {
        //     $data = [
        //         'transport' => $mailsetting->email_transport,
        //         'host' => $mailsetting->email_host,
        //         'port' => $mailsetting->email_port,
        //         'encryption' => $mailsetting->email_encryption,
        //         'username' => $mailsetting->email_username,
        //         'password' => $mailsetting->email_password,
        //         'timeout' => null,
        //         'auth_mode' => null,
        //     ];

        //     return [
        //         'default' => $mailsetting->email_transport,
        //         'mailers' => [
        //             $mailsetting->email_transport => $data,
        //         ],
        //         'from' => [
        //             'address' => $mailsetting->email_username ?? 'workflow@signaps.com',
        //             'name' => config('app.name'),
        //         ],
        //     ];

        //     return [];

        // }


        // Default to API configuration
        return [
            'default' => 'api',
            'mailers' => [
                'api' => [
                    'transport' => 'api',
                    'provider' => 'brevo',
                ],
            ],
            'from' => [
                'address' => 'workflow@signaps.com',
                'name' => config('app.name', 'Signaps.com'),
            ],
        ];
    }
}
