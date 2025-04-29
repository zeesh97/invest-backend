<?php

namespace App\Http\Controllers;

use App\Enums\FormEnum;
use App\Http\Helpers\Helper;
use App\Http\Resources\ServiceDeskResource;
use App\Http\Resources\ServiceDeskResourceCollection;
use App\Models\Form;
use App\Models\Scopes\FormDataAccessScope;
use App\Models\ServiceDesk;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\CustomRelations;
use Carbon\Carbon;

class ServiceDeskController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:ServiceDesk-view', ['only' => ['index', 'show']]);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $formId = (int) $request->form_id;
            $searchParams = $request->only([
                'sequence_no',
                'request_title',
                'location_id',
                'department_id',
                'created_by',
                'updated_by',
                'task_approval_at',
                'task_status',
                'status'
            ]);
            $sortBy = $request->input('sortBy', 'created_at');
            $sortOrder = $request->input('sortOrder', 'desc');

            $model = FormEnum::getModelById($formId);
            if (!$model) {
                return Helper::sendError('Invalid form ID', [], 400);
            }

            $relationships = [
                'assignedTasks',
                'taskStatusName',
                'user:id,name',
                'updatedBy:id,name',
                'department:id,name',
                'location:id,name',
                'taskStatus',
                'assignedTask:id,assignable_id,assignable_type,start_at,due_at,created_at',
                'assignedTask.assignTaskTeams.team:id,name',
                'assignedTask.assignTaskTeams.member:id,name,email',
                'assignedTask.assignTaskTeams.team.managers:id,name,email',
            ];

            $query = $model::with($relationships)
                ->withoutGlobalScope(FormDataAccessScope::class)
                ->where('status', 'Approved')
                ->select(['id', 'sequence_no', 'request_title', 'location_id', 'department_id', 'updated_by', 'task_status_at', 'task_status', 'status', 'comment_status', 'created_at', 'updated_at']);


            $validSortFields = [
                'sequence_no',
                'request_title',
                'created_at',
                'updated_at',
                'task_status_at',
                'task_initiated_at',
                'task_status'
            ];

            if (in_array($sortBy, $validSortFields)) {
                $query->orderBy($sortBy, $sortOrder);
            } else {
                // Handle relation sorting if needed
                switch ($sortBy) {
                    case 'location.name':
                        $query->leftJoin('locations', 'locations.id', '=', 'service_desks.location_id')
                            ->orderBy('locations.name', $sortOrder);
                        break;
                    case 'department.name':
                        $query->leftJoin('departments', 'departments.id', '=', 'service_desks.department_id')
                            ->orderBy('departments.name', $sortOrder);
                        break;
                    default:
                        $query->orderBy('created_at', 'desc'); // fallback default
                        break;
                }
            }

            // Apply search filters
            foreach ($searchParams as $key => $value) {
                if ($value !== null && $value !== '') {
                    if ($key == 'task_approval_at') {
                        $query->whereDate('task_status_at', Carbon::createFromFormat('d-m-Y', $value)->toDateString());
                    } elseif (in_array($key, ['location_id', 'department_id'])) {
                        $relation = substr($key, 0, -3);
                        $query->whereHas($relation, function ($q) use ($value) {
                            $q->where('id', $value);
                        });
                    } elseif ($key == 'task_assigned_teams_managers') {
                        $query->whereHas('assignedTask.assignTaskTeams.team.managers', function ($q) use ($value) {
                            $q->where('id', $value);
                        });
                    } else {
                        $query->where($key, 'like', '%' . $value . '%');
                    }
                }
            }

            $result = $query->latest()->paginate();

            return new ServiceDeskResourceCollection($result);
            // return Helper::sendResponse(ServiceDeskResource::collection($result), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 404); // Or 500 for a server error
        }
    }
}
