<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class CompanyResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'logo' => $this->logo ? config('app.url')."/".$this->logo: null,
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
