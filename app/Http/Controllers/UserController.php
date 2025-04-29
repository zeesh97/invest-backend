<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Helpers\SubscriptionHelper;
use App\Http\Requests\StoreUserRequest;
use App\Http\Requests\UpdateUserRequest;
use App\Http\Resources\BusinessExpertResource;
use App\Http\Resources\SoftwareSubcategoryResource;
use App\Http\Resources\UserResource;
use App\Http\Resources\UserUpdateResource;
use App\Models\Approver;
use App\Models\BusinessExpert;
use App\Models\CostCenter;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Equipment;
use App\Models\Location;
use App\Models\Section;
use App\Models\SoftwareCategory;
use App\Models\SoftwareSubcategory;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Services\UserService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class UserController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('total.user.check', ['only' => ['store']]);
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:User-create|User-edit|User-delete', ['only' => ['users']]);
            $this->middleware('role_or_permission:User-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:User-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:User-delete', ['only' => ['destroy']]);
        } else {
            // $this->middleware('CheckTotalUserLimit', ['only' => ['store']]);
            $this->middleware('role_or_permission:User-create|User-edit|User-delete', ['only' => ['users']]);
            $this->middleware('role_or_permission:User-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:User-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:User-delete', ['only' => ['destroy']]);
        }
    }

    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(User::latest()->select(['id', 'name', 'email', 'employee_no'])->get(), 'Success');
            } else {
                $perPage = $request->query('per_page', 10);
                $search = $request->query('search', '');
                return UserResource::collection(User::with('department:id,name', 'location:id,name', 'designation:id,name', 'section:id,name,department_id', 'roles:id,name', 'permissions', 'company:id,name,logo')
                    ->where('id', '!=', '1')
                    ->where(function ($query) use ($search) {
                        $query->whereLike([
                            'name',
                            'email',
                            'password',
                            'employee_no',
                            'employee_type',
                            'profile_photo_path',
                            'department.name',
                            'location.name',
                            'designation.name',
                            'section.name',
                            'extension',
                            'phone_number',
                            'company.name',
                            DB::raw('DATE_FORMAT(created_at, "%d/%m/%Y")'),
                        ], $search);
                    })->latest()->paginate($perPage));
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }


    public function store(StoreUserRequest $request, UserService $userService)
    {
        return $userService->store($request->validated(), $request);
    }

    public function update(UpdateUserRequest $request, UserService $userService, $id)
    {
        try {
            // dd($id);
            // $updatedUser = $userService->update($request->validated(), $request, $id);
            $validatedData = $request->validated();
            $validatedData['email'] = strtolower($validatedData['email']);
            $updatedUser = $userService->update($validatedData, $id);
            return Helper::sendResponse($updatedUser, 'User Profile updated successfully', 201);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function updateProfilePicture(Request $request, $user_id)
    {
        try {
            $user = User::find($user_id);

            if (!$user) {
                return Helper::sendError("Record not found", [], Response::HTTP_NOT_FOUND);
            }

            if (Auth::user()->hasRole('admin') || Auth::user()->id == $user_id) {
                $settings = new SettingService();
                $request->validate([
                    'profile_photo_path' => ['required', 'image', 'max:' . $settings->getMaxUploadSize()],
                ]);

                if ($request->hasFile('profile_photo_path')) {
                    $file = $request->file('profile_photo_path');

                    $fileSizeMb = $file->getSize() / (1024 * 1024);

                    // Check if subscription has enough available storage
                    SubscriptionHelper::updateSubscriptionUsage($fileSizeMb);

                    // Delete existing profile photo if it exists
                    if ($user->profile_photo_path) {
                        Storage::disk('public')->delete($user->profile_photo_path);
                    }

                    $filename = $this->generateFilename($file);
                    $profileDir = "profiles/{$user->id}";


                    // Ensure the directory exists and apply permissions
                    if (!Storage::disk('public')->exists($profileDir)) {
                        Storage::disk('public')->makeDirectory($profileDir);

                        // Get the absolute path and set permissions to 775
                        $publicDiskPath = Storage::disk('public')->path('/');
                        $absoluteDirPath = $publicDiskPath . $profileDir;
                        chmod($absoluteDirPath, 0775);
                    }

                    // Store the new profile picture
                    $file->storeAs($profileDir, $filename, 'public');

                    // Update the user record with the new profile photo path
                    $user->update([
                        'profile_photo_path' => "{$profileDir}/{$filename}",
                    ]);

                    // Include the full URL in the response
                    $user->profile_photo_path = asset("storage/{$profileDir}/{$filename}");

                    return Helper::sendResponse($user, 'User profile updated successfully');
                }
            } else {
                return Helper::sendError(
                    "Unauthorized",
                    [],
                    Response::HTTP_UNAUTHORIZED
                );
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    // Helper method to generate a unique filename
    private function generateFilename(UploadedFile $file): string
    {
        $uuid = Str::uuid();
        $timestamp = now()->format('YmdHis');
        $random = Str::random(8);

        return "{$timestamp}_{$uuid}_{$random}.{$file->getClientOriginalExtension()}";
    }

    public function updatePassword(Request $request, $id)
    {
        $user = User::find($id);;
        try {
            if (Auth::user()->hasRole('admin') || Auth::user()->id == $id) {
                $validated = $request->validate([
                    'password' => ['required', 'min:8', 'max:40'],
                    'password_confirmation' => ['required', 'same:password']
                ]);
                $user->update(['password' => Hash::make($validated['password'])]);


                return Helper::sendResponse($user, 'User password updated successfully', Response::HTTP_CREATED);
            } else {
                return Helper::sendError("Unauthorized Access Denied.", [], Response::HTTP_FORBIDDEN);
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function show(int $id): JsonResponse
    {
        try {
            $user = User::findOrFail($id);
            if ($user->hasRole('admin') || $id == Auth::user()->id) {
                return Helper::sendResponse(new UserResource($user), 'Success', 200);
            } else {
                return Helper::sendError("Unauthorized Access Denied.", [], Response::HTTP_FORBIDDEN);
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function users(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(User::latest()->select(['id', 'name'])->get(), 'Success');
            } else {
                $perPage = $request->query('perPage', 10);
                $search = $request->query('search', '');
                return UserResource::collection(User::where(function ($query) use ($search) {
                    $query->where('name', 'LIKE', "%$search%")
                        ->orWhere('email', 'LIKE', "%$search%");
                })->latest()->paginate($perPage));
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
    public function search(Request $request)
    {
        $query = $request->input('query');

        $users = User::where('name', 'like', "%{$query}%")
            ->take(3)
            ->get(['id', 'name', 'employee_no', 'profile_photo_path']);

        return Helper::sendResponse($users, 'Success', 200);
    }
    public function destroy(int $id): JsonResponse
    {
        try {
            $user = User::withTrashed()->find($id);

            if (
                DB::table('subscriber_user')->where('user_id', $id)->exists() ||
                DB::table('approver_users')->where('user_id', $id)->exists() ||
                DB::table('business_experts')->where('business_expert_user_id', $id)->exists() ||
                DB::table('quality_assurance_user')->where('assigned_to_id', $id)->exists() ||
                DB::table('quality_assurance_user')->where('created_by_id', $id)->exists() ||
                DB::table('workflow_initiator_fields')->where('initiator_id', $id)->exists() ||
                DB::table('workflows')->where('created_by_id', $id)->exists() ||
                DB::table('scrf')->where('created_by', $id)->exists()
            ) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $user->delete();
            return Helper::sendResponse($user, 'User deleted successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public function getAllLocations()
    {
        try {
            $data = Location::select(['id', 'name'])->get();
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Location not found.", 404);
        }
    }
    public function getAllCostCenters()
    {
        try {
            $data = CostCenter::with('department:id,name', 'location:id,name')
                ->select(['id', 'cost_center', 'department_id', 'location_id'])
                ->get();
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Cost center not found.", 404);
        }
    }
    public function getRelatedCostCenters()
    {
        try {
            $data = CostCenter::with('department:id,name', 'location:id,name')
                ->select(['id', 'cost_center', 'department_id', 'location_id'])
                ->where('location_id', Auth::user()->location_id)
                ->get();
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Cost center not found.", 404);
        }
    }
    public function getAllDesignations()
    {
        try {
            $data = Designation::select(['id', 'name'])->get();
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Designation not found.", 404);
        }
    }
    public function getAllDepartments()
    {
        try {
            $data = Department::select(['id', 'name'])->get();
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Department not found.", 404);
        }
    }
    public function getAllSections()
    {
        try {
            $data = Section::with('department:id,name')->select(['id', 'name', 'department_id'])->get();
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Section not found.", 404);
        }
    }
    public function getAllBusinessExperts()
    {
        try {
            $data = BusinessExpertResource::collection(BusinessExpert::latest()->get());
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Business Expert not found.", 404);
        }
    }

    public function getAllSoftwareCategories()
    {
        try {
            $data = SoftwareCategory::select('id', 'name')->get();
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Software Category not found.", 404);
        }
    }

    public function getAllSoftwareSubcategories(Request $request)
    {
        try {
            $data = SoftwareSubcategory::select('id', 'software_category_id', 'name')
                ->with(['software_category:id,name'])
                ->get();
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Software Subcategory not found.", 404);
        }
    }

    public function getSoftwareSubcategoriesByCategory($categoryId)
    {
        try {
            $data = SoftwareCategory::select('id', 'name')->where('id', $categoryId)
                ->with('software_subcategories:id,name,software_category_id')
                ->firstOrFail();

            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Software Subcategory not found.", 404);
        }
    }

    public function getAllUsers()
    {
        try {
            $data = User::with(
                'department:id,name',
                'designation:id,name',
                'location:id,name',
                'company:id,name,logo',
            )->select('id', 'name')->get();
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Software Category not found.", 404);
        }
    }

    public function getAllEquipments()
    {
        try {
            $data = Equipment::select('id', 'name')->get();
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Equipment not found.", 404);
        }
    }

    public function getAllApprovers()
    {
        try {
            $data = Approver::select('id', 'name')->get();
            return Helper::sendResponse($data, "Success", 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "Approver not found.", 404);
        }
    }
}
