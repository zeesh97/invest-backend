<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class StoreMobileRequisitionResource extends FormBaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $specificData =
        [
            // 'approved_disapproved' => $this->approved_disapproved ?? null,
            'issue_date' => $this->issue_date ?? null,
            'recieve_date' => $this->recieve_date ?? null,
            'make' => $this->make ?? null,
            'model' => $this->model ?? null,
            'imei' => $this->imei ?? null,
            'mobile_number' => $this->mobile_number ?? null,
            'remarks' => $this->remarks ?? null,
            'request_for_user' => $this->request_for_user ? (new UserResource($this->request_for_user))->only(['id', 'name']) : null,
            // 'attachments' => AttachmentResource::collection($this->attachments) ?: null,
        ];
        return array_merge(parent::toArray($request), $specificData);
    }
}
