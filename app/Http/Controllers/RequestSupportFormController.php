<?php

namespace App\Http\Controllers;

use App\Events\AssignPermissionToUsers;
use App\Http\Helpers\Helper;
use App\Http\Requests\StoreSupportDeskRequest;
use App\Http\Resources\RequestSupportFormResource;
use App\Jobs\SendAssignedTaskEmailJob;
use App\Models\AssignTask;
use App\Models\RequestSupportForm;
use App\Models\Team;
use App\Services\AttachmentService;
use App\Services\GlobalFormService;
use App\Traits\ModelDetails;
use Carbon\Carbon;
use Illuminate\Database\Eloquent\ModelNotFoundException;
use Illuminate\Http\Request;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\DB;
use Symfony\Component\HttpFoundation\Response;

class RequestSupportFormController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:RequestSupportForm-view|RequestSupportForm-create|RequestSupportForm-edit|RequestSupportForm-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:RequestSupportForm-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:RequestSupportForm-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:RequestSupportForm-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:RequestSupportForm-view|RequestSupportForm-create|RequestSupportForm-edit|RequestSupportForm-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:RequestSupportForm-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:RequestSupportForm-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:RequestSupportForm-delete', ['only' => ['destroy']]);
        }
    }
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request)
    {
        try {
            $query = RequestSupportForm::select(['id', 'request_title', 'sequence_no', 'relevant_id', 'priority', 'phone']);

            if ($request->has('request_title')) {
                $query->where('request_title', 'like', '%' . $request->input('request_title') . '%');
            }

            if ($request->has('sequence_no')) {
                $query->where('sequence_no', 'like', '%' . $request->input('sequence_no') . '%');
            }

            if ($request->has('relevant_id')) {
                $query->where('relevant_id', 'like', '%' . $request->input('relevant_id') . '%');
            }

            if ($request->has('priority')) {
                $query->where('priority', 'like', '%' . $request->input('priority') . '%');
            }

            if ($request->has('phone')) {
                $query->where('phone', 'like', '%' . $request->input('phone') . '%');
            }

            if ($request->has('all')) {
                $records = $query->latest()->get();
            } else {
                $perPage = $request->get('per_page', 10);
                $records = $query->latest()->paginate($perPage);
            }
            return Helper::sendResponse($records, 'Success', 200);
        } catch (\Exception $e) {
            \Log::error('Failed to fetch support desk forms: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }


    /**
     * Store a newly created resource in storage.
     */
    public function store(StoreSupportDeskRequest $request): JsonResponse
    {
        $validated = $request->validated();

        try {
            $globalFormService = new GlobalFormService();
            $sequenceNumber = $globalFormService->generateReferenceNumber(RequestSupportForm::class);
            $validated['sequence_no'] = $sequenceNumber;
            if ($request->save_as_draft === "false") {
                $record = RequestSupportForm::create([
                    'sequence_no' => $validated['sequence_no'],
                    'request_title' => $validated['request_title'],
                    'relevant_id' => $validated['relevant_id'],
                    'priority' => $validated['priority'],
                    'phone' => $validated['phone'],
                    'department_id' => $validated['department_id'],
                    'location_id' => auth()->user()->location_id,
                    'service_id' => $validated['service_id'],
                    'description' => $validated['description'],
                    'draft_at' => ($validated['save_as_draft'] === true) ? true : false,
                    'task_status_at' => now(),
                    'task_status' => 1,
                    'created_by' => auth()->user()->id,
                ]);

                if (!empty($validated['attachments'])) {
                    $attachments = new AttachmentService();
                    $attachments = $attachments->storeAttachment($validated['attachments'], $record->id, RequestSupportForm::class);

                    $record->attachments()->createMany($attachments);
                }

                $record->teams()->sync($validated['team_ids']);
                $teams = Team::with('managers:id,name')->whereIn('id', $validated['team_ids'])->get();
                $managerIds = $teams->pluck('managers.*.id')->flatten()->unique()->values()->toArray();

                $assignTask = AssignTask::create([
                    'assignable_id' => $record->id,
                    'assignable_type' => RequestSupportForm::class,
                    'task_assigned_by' => auth()->user()->id,
                ]);
                $teamMemberData = [];
                foreach ($teams as $team) {
                    foreach ($managerIds as $memberId) {
                        $teamMemberData[$team->id] = ['member_id' => $memberId];
                    }
                }

                $assignTask->assignedTeams()->attach($teamMemberData);
                dispatch(new SendAssignedTaskEmailJob($managerIds, $managerIds, $assignTask->toArray()));
                event(new AssignPermissionToUsers($managerIds, RequestSupportForm::class, $record->id));
            }
            return Helper::sendResponse($record, 'Success', 201);
        } catch (\Exception $e) {
            \Log::error('Failed to store support desk form: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return Helper::sendError('Failed to store support desk form: ' . $e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
        } catch (\Throwable $th) {
            \Log::error('Failed to store support desk form: ' . $th->getMessage(), [
                'exception' => $th,
            ]);
            return Helper::sendError('Failed to store support desk form: ' . $th->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Display the specified resource.
     */
    public function show($id)
    {
        try {
            $record = RequestSupportForm::with(
                'location:id,name',
                'department:id,name',
                'teams',
                'user:id,name,email,employee_no',
                'service:id,name',
                'attachables',
                'attachments'
            )->findOrFail($id);

            return new RequestSupportFormResource($record);
        } catch (ModelNotFoundException $e) {
            return Helper::sendError('Record not found.', [], Response::HTTP_NOT_FOUND);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch record: ' . $e->getMessage(), [], 500);
        }
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, RequestSupportForm $supportDeskForm): JsonResponse
    {
        return response()->json([]);
    }
    // public function assignTaskToTeams(array $teamIds)
    // {
    //     $managerIds = Team::with('managers:id,name')
    //         ->whereIn('id', $teamIds)->get();
    //     dd($managerIds);

    //     $assignTask = AssignTask::whereHas('assignTaskTeams', function ($query) {
    //         $query->where('member_id', auth()->user()->id);
    //     });

    //     if (!is_null($assignTask) && !is_null($assignTask->assignable)) {

    //         $result = DB::table($assignTask->assignable->getTable())->where('id', $assignTask->assignable->id)->update([
    //             'task_status_at' => Carbon::now(),
    //             'task_status' => $request->status
    //         ]);

    //         if ($result) {
    //             return Helper::sendResponse($result, 'Task status updated successfully!', 201);
    //         }
    //     }
    //     return Helper::sendError('Record not found.', [], 404);
    // }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(RequestSupportForm $supportDeskForm): JsonResponse
    {
        return response()->json([]);
    }
}
