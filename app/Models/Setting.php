<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Setting extends Model
{
    use HasFactory;
    protected $casts = [
        'timezone' => 'string',
    ];
    protected $fillable = [
        'max_upload_size', 'backup_notify_email', 'allowed_extensions',
        'email_transport', 'email_host', 'email_username', 'email_password', 'email_port',
        'email_encryption', 'email_driver', 'timezone'
    ];
}
