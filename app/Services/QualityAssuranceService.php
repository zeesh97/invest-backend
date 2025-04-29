<?php

namespace App\Services;

use App\Enums\FormEnum;
use App\Http\Resources\QualityAssuranceResource;
use App\Models\Form;
use App\Models\Forms\QualityAssurance;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use App\Http\Helpers\Helper;
use App\Models\QaAssignment;
use Auth;
use Exception;

class QualityAssuranceService extends BaseIndexService
{
    public function __construct()
    {
        parent::__construct(QualityAssurance::class, QualityAssuranceResource::class);
    }

    protected function getFilters()
    {
        return [
            'sequence_no',
            'request_title',
        ];
    }

    protected function getRelationships()
    {
        return [
            // 'software_category' => 'name',
            // 'software_subcategories' => 'name',
        ];
    }

    public static function storeQualityAssurance(array $storeData, $workflowId, $defined, $formId)
    {
        try {
            $isDraft = strtolower($storeData['save_as_draft']);

            $globalFormService = new GlobalFormService();
            $sequenceNumber = $globalFormService->generateReferenceNumber(QualityAssurance::class);

            $formIdentity = FormEnum::getModelById($storeData['request_form_id']);
            DB::beginTransaction();

            $qaAssignment = QaAssignment::where('qa_user_id', Auth::user()->id)
            ->where('assurable_id', $storeData['key'])
            ->where('assurable_type', $formIdentity)
            ->first();

            if (!$qaAssignment) {
                return Helper::sendError('You are not assigned for this request.', [], 433);
            }
            $qaAssignment->update([
                'status' => $storeData['status'],
                'feedback' => $storeData['feedback'],
                'status_at' => Carbon::now()
            ]);

            $request_title = $formIdentity::findORFail($storeData['key'])->request_title;


            $data = QualityAssurance::create([
                'sequence_no' => $sequenceNumber,
                'request_title' => $request_title,
                'qa_assignment_id' => $qaAssignment->id,
                'location_id' => Auth::user()->location_id,
                'department_id' => Auth::user()->department_id,
                'designation_id' => Auth::user()->designation_id,
                'section_id' => Auth::user()->section_id,
                'workflow_id' => $workflowId,
                'created_by' => Auth::user()->id,
                'draft_at' => ($isDraft === "false") ? null : Carbon::now(),
                'status' => ($isDraft === "true") ? 'Draft' : 'Pending'
            ]);

            if (!empty($storeData['attachments'])) {
                $attachments = new AttachmentService();
                $attachments = $attachments->storeAttachment($storeData['attachments'], $data->id, QualityAssurance::class);

                $data->attachments()->createMany($attachments);
            }

            $result = GlobalFormService::processApprovals($data, $defined, $workflowId, $formId);
            DB::commit();
            return Helper::sendResponse([], 'Successfully Added', 201);
        } catch (Exception $e) {
            DB::rollBack();
            \Log::error('Error in Quality Assurance Service: ' . $e->getMessage());
            return Helper::sendError($e->getMessage(), [], 433);
        }
    }
    // public static function storeQualityAssurance(array $storeData, $workflowId, $defined, $formId)
    // {
    //     try {
    //         // dd($storeData);
    //         $isDraft = strtolower($storeData['save_as_draft']);
    //         $globalFormService = new GlobalFormService();
    //         $sequenceNumber = $globalFormService->generateReferenceNumber(QualityAssurance::class);

    //         $form = Form::findOrFail($storeData['form_id']);
    //         $assurableType = $form->identity;
    //         $request_title = $assurableType::findORFail($storeData['key'])->request_title;

    //         $data = QualityAssurance::create([
    //             'assurable_id' => $storeData['key'],
    //             'assurable_type' => $assurableType,
    //             'testing_feedback' => $$storeData['testing_feedback'],
    //             'sequence_no' => $sequenceNumber,
    //             'request_title' => $request_title,
    //             'location_id' => $storeData['location_id'],
    //             'department_id' => Auth::user()->department_id,
    //             'designation_id' => Auth::user()->designation_id,
    //             'section_id' => Auth::user()->section_id,
    //             'workflow_id' => $workflowId,
    //             'created_by' => Auth::user()->id,
    //             'draft_at' => ($isDraft === "false") ? null : Carbon::now(),
    //             'status' => ($isDraft === "true") ? 'Draft' : 'Pending'
    //         ]);
    //         if ($data && !empty($storeData['assigned_to_ids'])) {
    //             $data->assignedToUsers()->attach(
    //                 $storeData['assigned_to_ids'],
    //                 [
    //                     'feedback' => null,
    //                     'status' => 'Opened'
    //                 ]
    //             );
    //         }

    //         if (!empty($storeData['attachments'])) {
    //             $attachments = new AttachmentService();
    //             $attachments = $attachments->storeAttachment($storeData['attachments'], $data->id, QualityAssurance::class);

    //             $data->attachments()->createMany($attachments);
    //         }

    //         if ($isDraft === "false") {
    //             $result = GlobalFormService::processApprovals($data, $defined, $workflowId, $formId);
    //             return Helper::sendResponse(new QualityAssuranceResource($result), 'Successfully Added', 201);
    //         } else {
    //             return Helper::sendResponse($data, 'Successfully saved as a Draft.', 201);
    //         }
    //     } catch (Exception $e) {
    //         \Log::error('Error in Storing: ' . $e->getMessage());
    //         return Helper::sendError($e->getMessage(), [], 433);
    //     }
    // }

    public function filterRecord(Request $request)
    {
        $perPage = $request->query('perPage', 10);
        $scrf = QualityAssurance::with([
            'loacation:id,name',
            'department:id,name',
        ]);

        $filters = [
            'sequence_no',
            'request_title',
        ];

        foreach ($filters as $filter) {
            $value = $request->$filter;

            if ($value) {
                $scrf->where($filter, 'LIKE', '%' . $value . '%');
            }
        }

        $relationships = [
            'loacation' => 'name',
            'department' => 'name',
        ];

        foreach ($relationships as $relationship => $column) {
            $value = $request->$relationship;

            // if ($value) {
            //     $scrf->whereHas($relationship, function ($query) use ($column, $value) {
            //         $query->where($column, 'LIKE', '%' . $value . '%');
            //     });
            // }
            if ($value) {
                // Use whereRelation to filter on the relationship's attribute
                $scrf->whereRelation($relationship, $column, 'LIKE', '%' . $value . '%');
            }
        }

        $sortBy = $request->query('sortBy', 'created_at');
        $sortOrder = $request->query('sortOrder', 'desc');

        if (in_array($sortBy, array_merge($filters, ['created_at', 'updated_at'])) && in_array($sortOrder, ['asc', 'desc'])) {
            $scrf->orderBy($sortBy, $sortOrder);
        } else {
            $scrf->latest();
        }

        return QualityAssuranceResource::collection($scrf->paginate($perPage));
    }
}
