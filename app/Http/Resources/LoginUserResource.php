<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class LoginUserResource extends JsonResource
{
    /**
     * Transform the resource into an array.
     *
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {

        return [
            'user_id' => $this->id,
            'name'    => $this->name,
            'email'    => $this->email,
            // 'departments' => $this->departments->pluck('name', 'id') ?? [],
            'department' => $this->department ? $this->department->only('id','name') : null,
            'company' => new CompanyResource($this->company),
            'location' => $this->location ? $this->location->only('id','name') : null,
            'designation' => $this->designation ? $this->designation->only('id','name') : null,
            'section' => $this->section ? $this->section->only('id','name') : null,
            'employee_no' => $this->employee_no,
            'employee_type' => $this->employee_type,
            'extension' => $this->extension,
            'phone_number' => $this->phone_number,
            'profile_photo_path' => config('app.url').'//uploads//'.$this->profile_photo_path,
            'token'    => $this->token,
            'impersonated_user' => $this->impersonated_user ? true : false,
            'roles'    => $this->roles->pluck('name') ?? [],
            'roles.permission' => $this->getPermissionsViaRoles() ?? [],
            'permissions'    => $this->permissions->pluck('name') ?? []
        ];
    }
}
