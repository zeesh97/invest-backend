<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class IndexSCRFResource extends IndexFormBaseResource
{
    public function toArray(Request $request): array
    {
        $specificData =
            [
                'request_specs' => $this->request_specs ?? null,
                'change_type' => $this->change_type ?? null,
                'change_priority' => $this->change_priority ?? null,
                'process_efficiency' => $this->truncateField($this->process_efficiency) ?? null,
                'cost_saved' => $this->truncateField($this->cost_saved) ?? null,
                'controls_improved' => $this->controls_improved ?? null,
                'legal_reasons' => $this->legal_reasons ?? null,
                'man_hours' => $this->man_hours ?? null,
                'other_benefits' => $this->other_benefits ?? null,
                'change_significance' => $this->change_significance ?? null,
                'software_category' => $this->software_category ? $this->software_category->only('id', 'name') : null,
                // 'software_subcategories' => $this->software_subcategories->isNotEmpty()
                //     ? $this->software_subcategories->map(function ($subcategory) {
                //         return [
                //             'id' => $subcategory->id,
                //             'name' => $subcategory->name,
                //             'software_category_id' => $subcategory->software_category_id ?? null,
                //         ];
                //     })
                //     : null,
                'software_subcategories' => $this->whenLoaded('software_subcategories', function () {
                    return $this->software_subcategories->map(function ($subcategory) {
                        return [
                            'id' => $subcategory->id,
                            'name' => $subcategory->name,
                            'software_category_id' => $subcategory->software_category_id ?? null,
                        ];
                    });
                }),

                'uatScenarios' => $this->uatScenarios && $this->uatScenarios->isNotEmpty() ?
                    UatScenarioResource::collection($this->uatScenarios) : null,
            ];
        return array_merge(parent::toArray($request), $specificData);
    }

    protected function truncateField($value): mixed
    {
        return strlen($value) > 40 ? substr($value, 0, 40) . '...' : $value;
    }
}
