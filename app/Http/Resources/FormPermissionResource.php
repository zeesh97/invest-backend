<?php

namespace App\Http\Resources;

use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

class FormPermissionResource extends JsonResource
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
            'form_role_name' => $this->name,
            'permissions' => $this->formPermissions->map(function ($formPermission) {
                return [
                    'form' => $formPermission->form->only('id', 'name'),
                    'type' => $formPermission->form_permissionable_type === "App\Models\Department"
                    ? ['id' => 1, 'name' => 'Department']
                    : ['id' => 2, 'name' => 'Location'],
                    'form_permissionable' => $formPermission->formPermissionable()->first(['id', 'name']),
                    'updated_at' => $formPermission->updated_at->format('Y-m-d H:i:s'),
                ];
            }),
        ];
    }
}
