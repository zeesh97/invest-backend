<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class IndexSapAccessFormResource extends IndexFormBaseResource
{
    public function toArray(Request $request): array
    {
        $specificData = [
            'data' => [
                'plant' => $this->data['plant'] ?? [],
                'company' => $this->company ?? null,
                'location' => $this->data['location'] ?? [],
                'sales_distribution' => $this->data['sales_distribution'] ?? [],
                'material_management' => $this->data['material_management'] ?? [],
                'plant_maintenance' => $this->data['plant_maintenance'] ?? [],
                'financial_accounting_costing' => $this->data['financial_accounting_costing'] ?? [],
                'production_planning' => $this->data['production_planning'] ?? [],
                'human_resource' => $this->data['human_resource'] ?? [],
            ],
        ];

        return array_merge(parent::toArray($request), $specificData);
    }

    protected function truncateField($value): mixed
    {
        return strlen($value) > 40 ? substr($value, 0, 40) . '...' : $value;
    }
}
