<?php

namespace App\Http\Controllers;

use App\Events\AssignPermissionToUsers;
use App\Http\Helpers\Helper;
use App\Http\Requests\QaAssignmentRequest;
use App\Http\Resources\QaAssignmentResource;
use App\Jobs\SendQaRequestEmailJob;
use App\Models\Form;
use App\Models\QaAssignment;
use Auth;
use Carbon\Carbon;
use Crypt;
use DB;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Validator;

class QaAssignmentController extends Controller
{
    protected $formData;
    /**
     * Display a listing of the resource.
     */
    public function index(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_id' => 'required|integer|exists:forms,id',
            'assurable_id' => 'nullable|integer', // Allow nullable assurable_id
        ]);
        $formIdentity = Form::findOrFail($validated['form_id'])->identity;
        $assurableId = $validated['assurable_id'] ?? null;
        $qaAssignment = QaAssignment::with([
            'qaUser:id,name',
            'qualityAssurances' => function ($query) {
                $query->select('id', 'qa_assignment_id', 'sequence_no', 'request_title', 'created_at');
                if (!Auth::user()->hasPermissionTo('QaFeedbackViewAll')) {
                    $query->where('created_by', Auth::user()->id);
                }
            }
        ])
            ->when($assurableId, function ($query, $assurableId) {
                $query->where('assurable_id', $assurableId);
            })
            ->where('assurable_type', $formIdentity)
            ->when(!Auth::user()->hasPermissionTo('QaFeedbackViewAll'), function ($query) {
                $query->where('qa_user_id', Auth::user()->id);
            })->get();
        return Helper::sendResponse(QaAssignmentResource::collection($qaAssignment), 'Success', 200);
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(QaAssignmentRequest $request): JsonResponse
    {
        try {
            $validatedData = $request->validated();
            $formIdentity = Form::findOrFail($validatedData['form_id'])->identity;
            $task_status = $formIdentity::findOrFail($validatedData['key'])->task_status;

            if ($task_status !== null && $task_status !== 6 && $task_status !== 7) {

                $insertData = $this->prepareInsertData($validatedData, $formIdentity);
                DB::beginTransaction();
                QaAssignment::insert($insertData);
                event(new AssignPermissionToUsers($validatedData['qa_user_ids'], $formIdentity, $validatedData['key']));
                dispatch(new SendQaRequestEmailJob($validatedData['key'], $validatedData['form_id'], $validatedData['qa_user_ids']));
                DB::commit();
                return Helper::sendResponse([], 'Quality assurance users assigned successfully', 200);
            }
            return Helper::sendError('Task status is not eligible to assign QA', [], Response::HTTP_UNPROCESSABLE_ENTITY);
        } catch (\Exception $exception) {
            DB::rollBack();
            return Helper::sendError($exception->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    private function prepareInsertData($validatedData, $formIdentity): array
    {
        $commonData = [
            'assigned_by' => auth()->user()->id,
            'assurable_type' => $formIdentity,
            'assurable_id' => $validatedData['key'],
            'created_at' => Carbon::now(),
        ];


        $existingQaUsers = QaAssignment::where('assurable_id', $validatedData['key'])
            ->where('assurable_type', $formIdentity)
            ->pluck('qa_user_id')
            ->toArray();

        $insertData = [];
        foreach ($validatedData['qa_user_ids'] as $qaUserId) {
            if (!in_array($qaUserId, $existingQaUsers)) {
                $insertData[] = array_merge($commonData, [
                    'qa_user_id' => $qaUserId,
                ]);
            }
        }

        return $insertData;
    }

    /**
     * Display the specified resource.
     */
    // public function show(string $id): JsonResponse
    // {
    //     return Helper::sendResponse([], 'Success', 200);
    // }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResponse
    {
        return Helper::sendResponse([], 'Success', 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id): JsonResponse
    // {
    //     return Helper::sendResponse([], 'Success', 200);
    // }

    public function ifQaAssigned(Request $request)
    {
        try {
            // dd('hii');
            // Decrypt the detail
            $decryptedDetail = Crypt::decryptString($request->input('detail'));

            // Decode the JSON to get form_id and key
            $data = json_decode($decryptedDetail, true);
            $form_id = $data['form_id'];
            $key = $data['key'];
            $rules = [
                'form_id' => [
                    'required',
                    'numeric',
                    'exists:forms,id'
                ],
                'key' => [
                    'required',
                    'numeric',
                    function ($attribute, $value, $fail) use ($form_id) {
                        $this->formData = Form::where('id', $form_id)
                        ->select(['id','identity','name'])->first();
                        $identity = $this->formData->identity;
                        if (!QaAssignment::where('assurable_type', $identity)->where('assurable_id', $value)
                        ->where('qa_user_id', Auth::user()->id)->exists()) {
                            $fail('Provided details are incorrect.');
                        }
                    },
                ],
            ];
            $validator = Validator::make($data, $rules);

            if ($validator->fails()) {
                return Helper::sendError($validator->errors()->first(), [], 404);
            }

            $modelIdentity = $this->formData->identity;
            $query = $modelIdentity::query();

            if ($form_id == 2) {
                $query->with('uatScenarios:id,detail,status,scrf_id');
            }

            $record = $query->select(['id', 'sequence_no', 'request_title'])
                            ->where('id', $key)
                            ->first();
            $form_name = $this->formData->name;

            return Helper::sendResponse(['form_id' => $form_id, 'form_name' => $form_name, 'record' => $record], 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Invalid details provided.', $e->getMessage(), 400);
        }
    }
    public function generateEncryptedDetail($form_id, $key)
    {
        $data = json_encode(['form_id' => $form_id, 'key' => $key]);
        return Crypt::encryptString($data);
    }

}
