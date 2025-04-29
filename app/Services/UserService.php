<?php

namespace App\Services;

// use App\Http\Resources\LoginUserResource;

use App\Http\Helpers\Helper;
use App\Http\Helpers\SubscriptionHelper;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\UserResource;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\User;
use Auth;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;
use Spatie\Permission\Models\Role;

class UserService
{

    public function store(array $userData, StoreUserRequest $request): JsonResponse
    {
        $user = User::create([
            'name' => $userData['name'],
            'email' => strtolower($userData['email']),
            'password' => Hash::make($userData['password']),
            'department_id' => $userData['department_id'],
            'location_id' => $userData['location_id'],
            'designation_id' => $userData['designation_id'],
            'section_id' => $userData['section_id'],
            'employee_no' => $userData['employee_no'],
            'employee_type' => $userData['employee_type'],
            'extension' => $userData['extension'] ?? null,
            'phone_number' => isset($userData['phone_number']) ? $userData['phone_number'] : null,
            'company_id' => $userData['company_id']

        ]);

        if (Auth::user()->hasRole('admin') && isset($userData['role_id'])) {
            // $user->assignRole($userData['role_id']);
            $roles = Role::whereIn('id', $userData['role_id'])->pluck('name')->toArray();
        $user->assignRole($roles);
        }
        $filename = '';
        if ($request->hasFile('profile_photo_path')) {
            $file = $request->file('profile_photo_path');
            $uuid = Str::uuid();
            $timestamp = now()->format('YmdHis');
            $random = Str::random(8);
            $filename = $timestamp . '_' . $uuid . '_' . $random . '.' . $file->getClientOriginalExtension();
            Storage::putFileAs('public/profiles/' . $user->id, $file, $filename, 'public');
            $user->update([
                'profile_photo_path' => 'storage/profiles/' . $user->id . '/' . $filename,
            ]);
        }
        if ($user->profile_photo_path) {
            $user->profile_photo_path = asset('storage/profiles/' . $user->id . '/' . $filename);
        }
        SubscriptionHelper::updateUsersLimitUsage(+1);
        return Helper::sendResponse($user, "Successfully Added", 201);
    }

    public function update(array $userData, $id): JsonResponse
    {
        $user = User::findOrFail($id);
        if (!Auth::user()->hasRole('admin') && $id != Auth::user()->id) {
            return Helper::sendError("Unauthorized Access Denied.", [], Response::HTTP_FORBIDDEN);
        }
        // var_dump('yes here');
        // var_dump($userData['department_id']);
        // var_dump($userData['company_id']);

        $user->update([
            'name' => $userData['name'] ?? $user->name,
            'password' => isset($userData['password']) ? Hash::make($userData['password']) : $user->password,
            'department_id' => $userData['department_id'] ?? $user->department_id,
            'location_id' => $userData['location_id'] ?? $user->location_id,
            'designation_id' => $userData['designation_id'] ?? $user->designation_id,
            'section_id' => $userData['section_id'] ?? $user->section_id,
            'employee_no' => $userData['employee_no'] ?? $user->employee_no,
            'employee_type' => $userData['employee_type'] ?? $user->employee_type,
            'extension' => $userData['extension'] ?? $user->extension,
            'email' => $userData['email'] ?? strtolower($user->email),
            'phone_number' => $userData['phone_number'] ?? $user->phone_number,
            'company_id' => $userData['company_id'] ?? $user->company_id

        ]);

        if (Auth::user()->hasRole('admin') && isset($userData['role_id'])) {
            $user->syncRoles($userData['role_id']);
        }

        if (isset($user->profile_photo_path)) {
            $user->profile_photo_path = asset($user->profile_photo_path);
        }
        return Helper::sendResponse($user, 'Successfully Updated', 201);
    }

    public function searchEmployeeName(Request $request): JsonResponse
    {
        $search = $request->get('search');
        return Helper::sendResponse(DB::table('users')
        ->select(DB::raw("CONCAT(name, ' / ', employee_no) as employee"))
            ->where(function ($query) use ($search) {
                $query->where('name', 'like', '%' . $search . '%')
                    ->orWhere('employee_no', 'like', '%' . $search . '%');
            })
            ->limit(5)
            ->get(), 'Success', 200);
    }
}
