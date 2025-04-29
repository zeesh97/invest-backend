<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CommentResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        // return parent::toArray($request);
        return [
            'id' => $this->id,
            'user' => $this->user ? $this->user->only('id', 'name', 'email', 'profile_photo_path') : null,
            'comment' => $this->comment,
            'created_at' => $this->created_at,
            // 'comment_attachments' => $this->attachments->map(function ($attachment) {
            //     return [
            //         'id' => $attachment->attachable_id,
            //         'filename' => config('app.url').'/'.$attachment->filename,
            //         'original_title' => $attachment->original_title,
            //     ];
            // }),
            'comment_attachments' => AttachmentResource::collection($this->attachables) ?: null,
        ];
    }
}
