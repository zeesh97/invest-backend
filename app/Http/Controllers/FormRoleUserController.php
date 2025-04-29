<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\FormRoleUserResource;
use App\Models\FormRoleUser;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class FormRoleUserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'perPage' => 'required|numeric|min:10|max:100'
            ]);
            // $formRoleUsers = User::select(['id', 'name'])
            //     ->join(
            //         DB::raw('(SELECT DISTINCT user_id FROM form_role_user LIMIT 10) AS limited_users'),
            //         'users.id',
            //         '=',
            //         'limited_users.user_id'
            //     )
            //     ->with(['formRoles:id,name'])
            //     ->paginate($validated['perPage']);

            $limitedUsers = DB::table('form_role_user')
                ->select('user_id')
                ->distinct()
                ->limit($validated['perPage']);

            $formRoleUsers = User::select(['users.id', 'users.name', 'users.employee_no'])
                ->joinSub($limitedUsers, 'limited_users', function ($join) {
                    $join->on('users.id', '=', 'limited_users.user_id');
                })
                ->with(['formRoles:id,name'])
                ->paginate($validated['perPage']);
                $formRoleUsers['status'] = 200;
            return $formRoleUsers;
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 401);
        }
    }

    /**
     * Show the form for creating a new resource.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'form_roles' => ['required', 'array'],
                'form_roles.*' => ['required', 'exists:form_roles,id'],
                'user_id' => ['required', 'exists:users,id']
            ]);
            $formRoleUsers = [];

            foreach ($validated['form_roles'] as $formRole) {
                if (!FormRoleUser::where('user_id', $validated['user_id'])
                    ->where('form_role_id', $formRole)
                    ->exists()) {
                    $formRoleUsers[] = [
                        'user_id' => $validated['user_id'],
                        'form_role_id' => $formRole
                    ];
                }
            }
            if (!empty($formRoleUsers)) {
                $result = FormRoleUser::insert($formRoleUsers);
                if (!$result) {
                    return Helper::sendError('Failed with error.', $result, 201);
                }
            }
            return Helper::sendResponse($formRoleUsers, 'Successfully Added', 201);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 401);
        }
    }

    public function update(Request $request, $id): JsonResponse
    {
        try {
            $validated = $request->validate([
                'form_roles' => ['required', 'array'],
                'form_roles.*' => ['required', 'exists:form_roles,id'],
                'user_id' => ['required', 'exists:users,id']
            ]);

            $formRoleUsers = FormRoleUser::where('user_id', $id)->exists();
            // dd($formRoleUser);
            if (!$formRoleUsers) {
                return Helper::sendError('No Record found.', [], 404);
            }
            FormRoleUser::where('user_id', $id)->delete();
            $formRoleUsers = [];

            foreach ($validated['form_roles'] as $formRole) {
                $formRoleUsers[] = [
                    'user_id' => $validated['user_id'],
                    'form_role_id' => $formRole
                ];
            }
            $result = FormRoleUser::insert($formRoleUsers);
            if (!$result) {
                return Helper::sendError('Failed with error.', $result, 201);
            }
            return Helper::sendResponse($formRoleUsers, 'Successfully Added', 201);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 401);
        }
    }


    public function destroy($id)
    {
        try {
            if (!Auth::user()->hasRole('admin')) {
                return Helper::sendError('You are not authorized to perform this.', [], 403);
            }
            $formRoleUsers = FormRoleUser::where('id', $id)->delete();
            if (!$formRoleUsers) {
                return Helper::sendError("Couldn't delete the user from form role.", [], 401);
            }
            return Helper::sendResponse([], 'User has been removed successfully.', 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 500);
        }
    }
}
