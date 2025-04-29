<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class IndexQualityAssuranceResource extends IndexFormBaseResource
{
    public function toArray(Request $request): array
    {
        $specificData =
        [
            'qa_tester' => $this->qaAssignment->qaUser->only(['id', 'name']) ?? null,
            'status' => $this->qaAssignment->status ?? null,
            'status_at' => $this->qaAssignment->status_at ?? null,
            'feedback' => $this->qaAssignment->feedback ?? null,
            'uatScenarios' => $this->uatScenarios?->isNotEmpty() ?
                UatScenarioResource::collection($this->uatScenarios) : null,
            'attachments' => AttachmentResource::collection($this->attachments) ?: null,
        ];
        return array_merge(parent::toArray($request), $specificData);
    }
}
