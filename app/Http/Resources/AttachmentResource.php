<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use URL;

class AttachmentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // $signedUrl = URL::temporarySignedRoute(
        //     config('app.url').'/'.$this->filename,
        //     now()->addMinutes(30),
        //     ['id' => $this->id]
        // );
        return [
            'id' => $this->id,
            'filename' => config('app.url').'/'.$this->filename,
            // 'filename' => $signedUrl,
            'original_title' => $this->original_title,
            // 'signed_url' => $signedUrl,
            // 'attachable_id' => $this->attachable_id,
            // 'attachable_type' => $this->attachable_type,
            'created_at' => $this->created_at,
        ];
    }
}
