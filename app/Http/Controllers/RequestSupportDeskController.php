<?php

namespace App\Http\Controllers;

use App\Enums\FormEnum;
use App\Http\Helpers\Helper;
use App\Http\Resources\RequestSupportDeskResource;
use App\Models\AssignTask;
use App\Models\Form;
use App\Models\Scopes\FormDataAccessScope;
use App\Models\RequestSupportDesk;
use App\Models\RequestSupportForm;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use App\Traits\CustomRelations;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class RequestSupportDeskController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:RequestSupportDesk-view', ['only' => ['index', 'show']]);
        }
    }

    // use CustomRelations;
    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'sequence_no' => 'nullable|string',
                'request_title' => 'nullable|string',
                'location_id' => 'nullable|integer',
                'department_id' => 'nullable|integer',
                'created_by' => 'nullable|integer',
                'updated_by' => 'nullable|integer',
                'task_status_at' => 'nullable|string',
                'task_status' => 'nullable|string',
                'status' => 'nullable|string',
            ]);
            // $formId = (int) $request->form_id;
            $searchParams = $request->only([
                'sequence_no',
                'request_title',
                'location_id',
                'department_id',
                'created_by',
                'updated_by',
                'task_status_at',
                'task_status',
                'status'
            ]);

            $relationships = [
                'assignedTasks',
                'taskStatusName',
                'user:id,name',
                'updatedBy:id,name',
                'department:id,name',
                'location:id,name',
                'taskStatus',
                'assignedTask',
            ];

            $query = RequestSupportForm::with($relationships)
                ->withoutGlobalScope(FormDataAccessScope::class)
                // ->where('status', 'Approved')
                ->select('id', 'sequence_no', 'request_title', 'location_id', 'department_id', 'updated_by', 'task_status_at', 'task_status', 'status', 'comment_status', 'created_at', 'updated_at');


            foreach ($searchParams as $key => $value) {
                if ($value !== null && $value !== '') {
                    if ($key == 'task_status_at') {
                        $query->whereDate($key, $value);
                    } elseif (in_array($key, ['location_id', 'department_id'])) {
                        $relation = substr($key, 0, -3);  // 'location' or 'department'
                        $query->whereHas($relation, function ($q) use ($value) {
                            $q->where('id', $value);
                        });
                    } else {
                        $query->where($key, 'like', '%' . $value . '%');
                    }
                }
            }

            $result = $query->latest()->paginate();

            return Helper::sendResponse(RequestSupportDeskResource::collection($result), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 404); // Or 500 for a server error
        }
    }
}
