<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyUpdateResource extends JsonResource
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
            'logo' => $this->profile_photo_path ? config('app.url')."/uploads/".$this->profile_photo_path: null,
            'code'    => $this->code,
            'name'    => $this->name,
            'long_name'    => $this->long_name,
            'ntn_no'    => $this->ntn_no,
            'sales_tax_no'    => $this->sales_tax_no,
            'postal_code'    => $this->postal_code,
            'address'    => $this->address,
            'phone'    => $this->phone,
        ];
    }
}
