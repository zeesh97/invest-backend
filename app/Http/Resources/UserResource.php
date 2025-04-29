<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class UserResource extends JsonResource
{
    public function toArray(Request $request): array
    {
        return [
            'id' => $this->id,
            'name'    => $this->name,
            'email'    => $this->email,
            'department' => new DepartmentResource($this->department),
            'company' => new CompanyResource($this->company),
            'location' => new LocationResource($this->location),
            'designation' => new DesignationResource($this->designation),
            'business_expert' => $this->businessExpert,
            'section' => new SectionResource($this->section),
            'employee_no' => $this->employee_no,
            'employee_type' => $this->employee_type,
            'extension' => $this->extension,
            'phone_number' => $this->phone_number,
            'profile_photo_path' => $this->profile_photo_path ? config('app.url')."/uploads/".$this->profile_photo_path: null,
            'roles'    => $this->roles->map(function($role){
                return [
                    'id' => $role->id,
                    'name' => $role->name
                ];
            }),
            'roles_permission' => $this->getPermissionsViaRoles() ?? [],
            'permissions'    => $this->permissions->pluck('name') ?? []
        ];
    }
}
