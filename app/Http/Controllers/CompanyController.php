<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Requests\StoreCompanyRequest;
use App\Http\Requests\UpdateCompanyRequest;
use App\Http\Resources\BusinessExpertResource;
use App\Http\Resources\SoftwareSubcategoryResource;
use App\Http\Resources\CompanyResource;
use App\Http\Resources\CompanyUpdateResource;
use App\Models\Company;
use App\Models\User;
use App\Services\SettingService;
use Illuminate\Contracts\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Validation\ValidationException;
use App\Services\CompanyService;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\HttpFoundation\Response;

class CompanyController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            // $this->middleware('auth:sanctum');
            // $this->middleware('role_or_permission:User-create|User-edit|User-delete', ['only' => ['users']]);
            // $this->middleware('role_or_permission:User-create', ['only' => ['create', 'store']]);
            // $this->middleware('role_or_permission:User-edit', ['only' => ['edit', 'update']]);
            // $this->middleware('role_or_permission:User-delete', ['only' => ['destroy']]);
        } else {
            // $this->middleware('role_or_permission:User-create|User-edit|User-delete', ['only' => ['users']]);
            // $this->middleware('role_or_permission:User-create', ['only' => ['create', 'store']]);
            // $this->middleware('role_or_permission:User-edit', ['only' => ['edit', 'update']]);
            // $this->middleware('role_or_permission:User-delete', ['only' => ['destroy']]);
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
                return Company::select([
                    'id',
                    'logo',
                    'code',
                    'name',
                    'long_name',
                    'ntn_no',
                    'sales_tax_no',
                    'postal_code',
                    'address',
                    'phone',
                ])
                ->where(function ($query) use ($search) {
                    $query->whereLike([
                        'logo',
                        'code',
                        'name',
                        'long_name',
                        'ntn_no',
                        'sales_tax_no',
                        'postal_code',
                        'address',
                        'phone',
                    ], $search);
                })
                ->latest()
                ->paginate($perPage);
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }


    public function store(StoreCompanyRequest $request, CompanyService $companyService)
    {
        return $companyService->store($request->validated(), $request);
    }

    public function update(UpdateCompanyRequest $request, CompanyService $companyService, $id)
    {
        try {
            $validatedData = $request->validated();
            $updatedUser = $companyService->update($validatedData, $id, $request);
            return Helper::sendResponse($updatedUser, 'Company updated successfully', 201);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }

    }

    public function show(int $id): JsonResponse
    {
        try {
            $user = Company::findOrFail($id);
            if ($user->hasRole('admin') || $id == Auth::user()->id) {
                return Helper::sendResponse(new UserResource($user), 'Success', 200);
            } else {
                return Helper::sendError("Unauthorized Access Denied.", [], Response::HTTP_FORBIDDEN);
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function companies(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(User::latest()->select(['id', 'name'])->get(), 'Success');
            } else {
                $perPage = $request->query('perPage', 10);
                $search = $request->query('search', '');
                return CompanyResource::collection(User::where(function ($query) use ($search) {
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
            ->get(['id', 'name', 'employee_no', 'logo']);

        return Helper::sendResponse($users, 'Success', 200);
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $company = Company::find($id);

            if (
                User::withTrashed()->where('company_id', $id)->exists()
            ) {
                return Helper::sendError("Cannot delete. This reference is in use.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            } else {
                $company->delete();
                return Helper::sendResponse($company, 'Company deleted successfully');
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


}
