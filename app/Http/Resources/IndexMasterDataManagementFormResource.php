<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Support\Facades\DB;

class IndexMasterDataManagementFormResource extends IndexFormBaseResource
{
    public function toArray(Request $request): array
    {
        $specificData =
            [
                'request_specs' => $this->request_specs ?? null,
                'change_priority' => $this->change_priority ?? null,
                'software_category' => $this->software_category ? $this->software_category->only('id', 'name') : null,
                'mdm_category' => $this->mdm_category ? $this->mdm_category->only('id', 'name') : null,
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
                    UatScenarioMDMResource::collection($this->uatScenarios) : null,
            ];
        return array_merge(parent::toArray($request), $specificData);
    }
}
