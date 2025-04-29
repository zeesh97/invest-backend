<?php

namespace App\Services;

use App\Http\Helpers\Helper;
use App\Http\Helpers\SubscriptionHelper;
use App\Models\Subscription;
use DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Log;

class AttachmentService
{
    public function storeAttachment(array $storeData, int $modelId, string $modelClass)
    {
        $attachments = [];
        $totalSizeMb = 0;

        foreach ($storeData as $attachment) {
            if (!$attachment->isValid()) {
                throw new \Exception("Invalid file upload.");
            }
            $fileSizeMb = $attachment->getSize() / (1024 * 1024);
            $totalSizeMb += $fileSizeMb;

            $uuid = str::uuid();
            $timestamp = now()->format('YmdHis');
            $random = Str::random(8);
            $filename = $timestamp . '_' . $uuid . '_' . $random . '.' . $attachment->extension();
            $originalTitle = $attachment->getClientOriginalName();
            $prefix = GlobalFormService::generateSlug($modelClass);

            Storage::disk('public')->makeDirectory('attachments/' . $prefix . '/' . $modelId);
            $attachment->storeAs('attachments/' . $prefix . '/' . $modelId, $filename, 'public');

            $url = 'uploads/attachments/' . $prefix . '/' . $modelId . '/' . $filename;

            $attachments[] = [
                'attachable_id' => $modelId,
                'attachable_type' => $modelClass,
                'filename' => $url,
                'original_title' => $originalTitle
            ];
        }
        SubscriptionHelper::updateSubscriptionUsage($totalSizeMb);

        return $attachments;
    }
}
