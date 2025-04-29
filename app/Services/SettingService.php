<?php

namespace App\Services;

use App\Http\Helpers\Helper;
use App\Jobs\ClearCacheJob;
use App\Models\Setting;
use Artisan;
use Symfony\Component\HttpFoundation\Response;

class SettingService
{
    public function update(array $data): Setting
    {
        $setting = Setting::first();

        $setting->update($data);

        return $setting;
    }

    public function getAllowedExtensions()
    {
        $setting = Setting::first();
        $decodedArray = $setting['allowed_extensions'];
        return $decodedArray;
    }

    public function getMaxUploadSize(): int
    {
        $setting = Setting::first();

        return $setting->max_upload_size;
    }

    public function getEmailSettings()
    {
        $setting = Setting::first();
        $emailSettings = [
            'host' => $setting['email_host'],
            'username' => $setting['email_username'],
            'password' => $setting['email_password'],
            'port' => $setting['email_port'],
            'encryption' => $setting['email_encryption'],
            'email_transport' => $setting['email_transport']
        ];
        ClearCacheJob::dispatch();
        return $emailSettings;
    }
}
