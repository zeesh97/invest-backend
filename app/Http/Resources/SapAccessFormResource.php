<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class SapAccessFormResource extends FormBaseResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */

    public function toArray(Request $request): array
    {
        // $data = [
        //     'plant' => $this->plant ?? null,
        //     'company' => $this->company->only(['id', 'name']) ?? null,
        //     'location' => $this->location ?? null,
        //     'sales_distribution' => $this->data['sales_distribution'] ?? [],
        //     'material_management' => $this->data['material_management'] ?? [],
        //     'plant_maintenance' => $this->data['plant_maintenance'] ?? [],
        //     'financial_accounting_costing' => $this->data['financial_accounting_costing'] ?? [],
        //     'production_planning' => $this->data['production_planning'] ?? [],
        //     'human_resource' => $this->data['human_resource'] ?? [],
        // ];
        // dd($data );
        $specificData = [
            'sap_id' => $this->sap_id,
            'type' => $this->type,
            'roles_required' => $this->roles_required,
            'tcode_required' => $this->tcode_required,
            'business_justification' => $this->business_justification,
            'company' => $this->company?->only(['id', 'name']) ?? null,
            // 'company_id' => $this->company_id,
            'data' => $this->data,
            'attachments' => AttachmentResource::collection($this->attachables) ?: null,
        ];

        return array_merge(parent::toArray($request), $specificData);
    }

    protected function truncateField($value): mixed
    {
        return strlen($value) > 40 ? substr($value, 0, 40) . '...' : $value;
    }
}
