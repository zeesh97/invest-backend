<?php

namespace App\Http\Resources;

use Auth;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class IndexFormBaseResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'sequence_no' => $this->sequence_no,
            'request_title' => $this->truncateField($this->request_title),
            // 'approvers' => $this->mapApprovers(),
            'status' => $this->status ?? null,
            // 'task_status' => $this->taskStatus ?? null,
            'comment_status' => $this->comment_status,
            'draft_at' => $this->draft_at ?? null,
            'created_by' => $this->user ? $this->user->only('id', 'name') : null,
            'created_at' => $this->created_at ? $this->created_at : null,
            'department' => isset($this->department) ? $this->department : null,
            'location' => isset($this->location) ? new LocationResource($this->location) : null,
            'designation' => isset($this->designation) ? new DesignationResource($this->designation) : null,
            'section' => isset($this->section) ? new SectionResource($this->section) : null,
            'editable' => $this->checkEditable(),
        ];
    }


    private function checkEditable(): bool
    {
        return ($this->status === 'Return' && $this->created_by === Auth::user()->id) ||
           ($this->draft_at !== null && $this->created_by === Auth::user()->id) ||
           $this->approvalStatuses
               ->where('status', 'Processing')
               ->where('user_id', Auth::user()->id)
               ->where('editable', 1)
               ->isNotEmpty();
    }
    protected function truncateField($value): mixed
    {
        return strlen($value) > 40 ? substr($value, 0, 40) . '...' : $value;
    }
}
