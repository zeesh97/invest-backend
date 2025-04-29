<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class IndexMobileRequisitionResource extends IndexFormBaseResource
{
    public function toArray(Request $request): array
    {
        $specificData =
        [
            // 'id' => $this->id,
            // 'approved_disapproved' => $this->approved_disapproved ?? null,
            'issue_date' => $this->issue_date ?? null,
            'recieve_date' => $this->recieve_date ?? null,
            // 'make' => $this->make ?? null,
            'make' => $this->makeRelation ? (new MakeResource($this->makeRelation))->only(['id', 'name']) : null,
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
