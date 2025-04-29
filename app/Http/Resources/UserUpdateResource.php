<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserUpdateResource extends JsonResource
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
            'name'    => $this->name,
            'email'    => $this->email,
            'department' => $this->departments->pluck('name', 'id') ?? [],
            'company' => $this->company->pluck('name', 'id') ?? [],
            'designation' => $this->designation->only('id','name') ?? [],
            'location' => $this->location->only('id','name') ?? [],
            'employee_no' => $this->employee_no,
            'employee_type' => $this->employee_type,
            'extension' => $this->extension,
            'phone_number' => $this->phone_number,
            'profile_photo_path' => $this->profile_photo_path,
            'token'    => $this->createToken("Token")->plainTextToken,
            'roles'    => $this->roles->pluck('name') ?? [],
            'roles.permission' => $this->getPermissionsViaRoles() ?? [],
            'permissions'    => $this->permissions->pluck('name') ?? []
        ];
    }
}
