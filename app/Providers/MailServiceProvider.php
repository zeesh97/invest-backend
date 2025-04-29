<?php

namespace App\Providers;

use App\Models\Setting;
use Illuminate\Support\ServiceProvider;

class MailServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        $mail = Setting::first();

    if (isset($mail->id))
    {
      $config = array(
        'driver'     => $mail->email_driver,
        'host'       => $mail->email_host,
        'port'       => $mail->email_port,
        'encryption' => $mail->email_encryption,
        'username'   => $mail->email_username,
        'password'   => $mail->email_password
      );

      \Config::set('mail', $config);
    }
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
    }
}
