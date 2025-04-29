<?php

namespace App\Services;

use App\Enums\FormEnum;
use App\Http\Resources\CRFResource;
use App\Http\Resources\StoreCRFResource;
use App\Http\Resources\UpdateCRFResource;
use App\Models\ApprovalStatus;
use App\Models\CostCenter;
use App\Models\Forms\CRF;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Helpers\Helper;
use App\Models\OtherDependent;
use Auth;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CRFService extends BaseIndexService
{
    protected $model;
    protected $resourceClass;
    protected $defaultCurrency;

    protected $currentUser;

    public function __construct()
    {
        $this->model = CRF::class;
        $this->resourceClass = CRFResource::class;
        parent::__construct($this->model, $this->resourceClass);
        $this->defaultCurrency = $this->getDefaultCurrency();
        $this->currentUser = Auth::user();
    }
    private function getDefaultCurrency()
    {
        return OtherDependent::where('type', 'crf')->first()->data[0]['crf_currency'];
    }
    protected function getFilters()
    {
        return [
            'sequence_no',
            'request_title',
            'status'
        ];
    }

    protected function getRelationships()
    {
        return [
            'costCenter' => 'project',
            'location' => 'name',
        ];
    }
    public function storeService(array $storeData, $workflowId, $defined, $formId)
    {
        // dd($this->currentUser);
        // // $costCenter = CostCenter::where('location_id', $storeData['location_id'])
        // //     ->where('department_id', $this->currentUser->department_id)->first();
        // // if (!$costCenter) {
        // //     return Helper::sendError('Cost Center does not exist for this location and department. Please define it first.', [], 433);
        // // }
        DB::beginTransaction();

        $record = $this->model::create([
            'sequence_no' => $storeData['sequence_no'],
            'request_title' => $storeData['request_title'],
            // 'cost_center_id' => $costCenter->id,
            'cost_center_id' => $storeData['cost_center_id'],
            'location_id' => $storeData['location_id'],
            'department_id' => $storeData['department_id'],
            'for_department' => $storeData['for_department'],
            'for_employee' => $storeData['for_employee'],
            'designation_id' => $this->currentUser->designation_id,
            'section_id' => $this->currentUser->section_id,
            'workflow_id' => $workflowId,
            'created_by' => $this->currentUser->id,
            'draft_at' => null,
            'status' => 'Pending'
        ]);

        if (isset($storeData['equipment_requests'])) {
            $equipmentRequests = [];

            foreach ($storeData['equipment_requests'] as $key => $request) {
                $equipmentRequests[] = [
                    'qty' => $request['quantity'],
                    'state' => $request['state'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'equipment_id' => $request['equipment_id'],
                    'crf_id' => $record->id,
                ];
            }
            $record->equipmentRequests()->createMany($equipmentRequests);
        }

        if (isset($storeData['software_requests'])) {
            foreach ($storeData['software_requests'] as $key => $request) {
                $softwareRequests[] = [
                    'qty' => $request['quantity'],
                    'name' => $request['software_name'],
                    'version' => $request['version'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'crf_id' => $record->id,
                ];
            }
            $record->softwareRequests()->createMany($softwareRequests);
        }

        if (isset($storeData['service_requests'])) {
            foreach ($storeData['service_requests'] as $key => $request) {
                $serviceRequests[] = [
                    'name' => $request['service_name'],
                    'state' => $request['state'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'crf_id' => $record->id,
                ];
            }
            $record->serviceRequests()->createMany($serviceRequests);
        }


        $result = GlobalFormService::processApprovals($record, $defined, $workflowId, $formId);
        $notifiedUserIds = array_unique(array_merge(
            $result['approverIds'],
            // $result['subscriberIds'],
            $result['parallelApproverIds'],
            [$result['created_by']]
        ));
        DB::commit();
        return ['notifiedUserIds' => $notifiedUserIds, 'data' => $result['resultData']];
    }

    // public static function storeService(array $storeData, $workflowId, $defined, $formId)
    // {
    //     try {
    //         $costCenter = CostCenter::where('location_id', $storeData['location_id'])
    //             ->where('department_id', $this->currentUser->department_id)->first();
    //         if (!$costCenter) {
    //             return Helper::sendError('Cost Center does not exist for this location and department. Please define it first.', [], 433);
    //         }
    //         $isDraft = strtolower($storeData['save_as_draft']);

    //         $globalFormService = new GlobalFormService();
    //         $sequenceNumber = $globalFormService->generateReferenceNumber(CRF::class);

    //         $record = CRF::create([
    //             'sequence_no' => $sequenceNumber,
    //             'request_title' => $storeData['request_title'],
    //             'location_id' => $storeData['location_id'],
    //             'department_id' => $this->currentUser->department_id,
    //             'designation_id' => $this->currentUser->designation_id,
    //             'section_id' => $this->currentUser->section_id,
    //             'cost_center_id' => $costCenter->id,
    //             'workflow_id' => $workflowId,
    //             'created_by' => $this->currentUser->id,
    //             'draft_at' => ($isDraft === 'false') ? null : Carbon::now(),
    //             'status' => ($isDraft === "true") ? 'Draft' : 'Pending'
    //         ]);
    //         if (isset($storeData['equipment_requests'])) {
    //             $equipmentRequests = [];

    //             foreach ($storeData['equipment_requests'] as $key => $request) {
    //                 $equipmentRequests[] = [
    //                     'qty' => $request['quantity'],
    //                     'state' => $request['state'],
    //                     'expense_type' => $request['expense_type'],
    //                     'expense_nature' => $request['expense_nature'],
    //                     'business_justification' => $request['business_justification'],
    //                     'equipment_id' => $request['equipment_id'],
    //                     'crf_id' => $record->id,
    //                 ];
    //             }
    //             $record->equipmentRequests()->createMany($equipmentRequests);
    //         }

    //         if (isset($storeData['software_requests'])) {
    //             foreach ($storeData['software_requests'] as $key => $request) {
    //                 $softwareRequests[] = [
    //                     'qty' => $request['quantity'],
    //                     'name' => $request['software_name'],
    //                     'version' => $request['version'],
    //                     'expense_type' => $request['expense_type'],
    //                     'expense_nature' => $request['expense_nature'],
    //                     'business_justification' => $request['business_justification'],
    //                     'crf_id' => $record->id,
    //                 ];
    //             }
    //             $record->softwareRequests()->createMany($softwareRequests);
    //         }

    //         if (isset($storeData['service_requests'])) {
    //             foreach ($storeData['service_requests'] as $key => $request) {
    //                 $serviceRequests[] = [
    //                     'name' => $request['service_name'],
    //                     'state' => $request['state'],
    //                     'expense_type' => $request['expense_type'],
    //                     'expense_nature' => $request['expense_nature'],
    //                     'business_justification' => $request['business_justification'],
    //                     'crf_id' => $record->id,
    //                 ];
    //             }
    //             $record->serviceRequests()->createMany($serviceRequests);
    //         }


    //         $result = GlobalFormService::processApprovals($record, $defined, $workflowId, $formId);
    //         return Helper::sendResponse(new CRFResource($result), 'Successfully Added', 201);
    //     } catch (\Exception $e) {
    //         \Log::error('Error in CRFService: ' . $e->getMessage());
    //         return Helper::sendError($e->getMessage(), [], 433);
    //     }
    // }
    public function draftService(array $storeData)
    {

        $array = [
            'sequence_no' => $storeData['sequence_no'],
            'request_title' => $storeData['request_title'],
            'cost_center_id' => $storeData['cost_center_id'],
            'location_id' => $storeData['location_id'],
            'department_id' => $storeData['department_id'],
            'designation_id' => $this->currentUser->designation_id,
            'section_id' => $this->currentUser->section_id,
            'for_department' => $storeData['for_department'],
            'for_employee' => $storeData['for_employee'],
            'workflow_id' => null,
            'created_by' => $this->currentUser->id,
            'draft_at' => Carbon::now(),
            'status' => 'Draft'
        ];
        DB::beginTransaction();
        $record = $this->model::updateOrCreate(
            $array
        );
        if (isset($storeData['equipment_requests'])) {
            $equipmentRequests = [];

            foreach ($storeData['equipment_requests'] as $key => $request) {
                $equipmentRequests[] = [
                    'qty' => $request['quantity'],
                    'state' => $request['state'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'equipment_id' => $request['equipment_id'],
                    'crf_id' => $record->id,
                ];
            }
            $record->equipmentRequests()->createMany($equipmentRequests);
        }

        if (isset($storeData['software_requests'])) {
            foreach ($storeData['software_requests'] as $key => $request) {
                $softwareRequests[] = [
                    'qty' => $request['quantity'],
                    'name' => $request['software_name'],
                    'version' => $request['version'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'crf_id' => $record->id,
                ];
            }
            $record->softwareRequests()->createMany($softwareRequests);
        }

        if (isset($storeData['service_requests'])) {
            foreach ($storeData['service_requests'] as $key => $request) {
                $serviceRequests[] = [
                    'name' => $request['service_name'],
                    'state' => $request['state'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'crf_id' => $record->id,
                ];
            }
            $record->serviceRequests()->createMany($serviceRequests);
        }
        DB::commit();
        return $record;
    }
    public function updateByEditorService(array $updateData, $record, $formId)
    {
        // dd($updateData['save_as_draft']);
        $array = [
            'request_title' => $updateData['request_title'],
            'cost_center_id' => $updateData['cost_center_id'],
            'location_id' => $updateData['location_id'],
            'department_id' => $updateData['department_id'],
            'for_department' => $updateData['for_department'],
            'for_employee' => $updateData['for_employee'],
        ];

        DB::beginTransaction();
        $record->fill($array);
        $record->save();
        $record->equipmentRequests()->delete();
        $record->softwareRequests()->delete();
        $record->serviceRequests()->delete();
        if (isset($updateData['equipment_requests'])) {
            $equipmentRequests = [];

            foreach ($updateData['equipment_requests'] as $key => $request) {

                // CALCULATE TOTAL BY SUM OF EXPENSE RATE
                $assetDetailJson = json_decode($request['asset_details'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo 'JSON decoding error: ' . json_last_error_msg();
                } else {
                    $expectedExpenses = array_column($assetDetailJson, 'expected_expense');
                    $totalExpectedExpense = array_sum($expectedExpenses);
                }
                // CALCULATE TOTAL BY EXPENSE RATE

                $equipmentRequests[] = [
                    'crf_id' => $record->id,
                    'qty' => $request['quantity'],
                    'state' => $request['state'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'equipment_id' => $request['equipment_id'],
                    'amount' => $request['amount'],
                    'currency_default' => $this->defaultCurrency,
                    'currency' => $request['currency'],
                    'rate' => $request['rate'],
                    // 'total' => $request['amount'] * $request['rate'],
                    'total' => $totalExpectedExpense,
                    'asset_details' => $request['asset_details'],

                ];
            }
            $record->equipmentRequests()->createMany($equipmentRequests);
        }
        if (isset($updateData['software_requests'])) {

            foreach ($updateData['software_requests'] as $key => $request) {

                // CALCULATE TOTAL BY SUM OF EXPENSE RATE
                $assetDetailJson = json_decode($request['asset_details'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo 'JSON decoding error: ' . json_last_error_msg();
                } else {
                    $expectedExpenses = array_column($assetDetailJson, 'expected_expense');
                    $totalExpectedExpense = array_sum($expectedExpenses);
                }
                // CALCULATE TOTAL BY EXPENSE RATE

                $softwareRequests[] = [
                    'crf_id' => $record->id,
                    'qty' => $request['quantity'],
                    'name' => $request['software_name'],
                    'version' => $request['version'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'amount' => $request['amount'],
                    'currency_default' => $this->defaultCurrency,
                    'currency' => $request['currency'],
                    'rate' => $request['rate'],
                    'total' => $totalExpectedExpense,
                    // 'total' => $request['amount'] * $request['rate'],
                    'asset_details' => $request['asset_details']

                ];
            }
            $record->softwareRequests()->createMany($softwareRequests);
        }

        if (isset($updateData['service_requests'])) {



            foreach ($updateData['service_requests'] as $key => $request) {
                // CALCULATE TOTAL BY SUM OF EXPENSE RATE
                $assetDetailJson = json_decode($request['asset_details'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    echo 'JSON decoding error: ' . json_last_error_msg();
                } else {
                    $expectedExpenses = array_column($assetDetailJson, 'expected_expense');
                    $totalExpectedExpense = array_sum($expectedExpenses);
                }
                // CALCULATE TOTAL BY EXPENSE RATE

                $serviceRequests[] = [
                    'crf_id' => $record->id,
                    'name' => $request['service_name'],
                    'state' => $request['state'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'amount' => $request['amount'],
                    'currency_default' => $this->defaultCurrency,
                    'currency' => $request['currency'],
                    'rate' => $request['rate'],
                    'total' => $totalExpectedExpense,
                    // 'total' => $request['amount'] * $request['rate'],
                    'asset_details' => $request['asset_details']

                ];
            }
            $record->serviceRequests()->createMany($serviceRequests);
        }
        if ($updateData['save_as_draft'] !== 'false') {
            $check = ApprovalStatus::where('form_id', $formId)
                ->where('key', $record->id)
                ->where('user_id', $this->currentUser->id)->first();
            if ($check && $check->condition_id == 9) {
                GlobalFormService::approvalConditions($record, $formId, $check->condition_id, $check->sequence_no, $check->approver_id);
            }
        }
        DB::commit();
        activity()
            ->performedOn($record)
            ->createdAt(now())
            ->event('Form fields updated')
            ->log('edited');
        return $record;
    }

    //     public static function draftService(array $storeData, $formId)
    // {
    //     try {
    //         $costCenter = CostCenter::where('location_id', $storeData['location_id'])
    //             ->where('department_id', $this->currentUser->department_id)->first();
    //         if (!$costCenter) {
    //             return Helper::sendError('Cost Center does not exist for this location and department. Please define it first.', [], 433);
    //         }
    //         $isDraft = strtolower($storeData['save_as_draft']);
    //         $globalFormService = new GlobalFormService();
    //         $sequenceNumber = $globalFormService->generateReferenceNumber(CRF::class);

    //         $array = [
    //             'sequence_no' => $sequenceNumber,
    //             'request_title' => $storeData['request_title'],
    //             'location_id' => $storeData['location_id'],
    //             'department_id' => $this->currentUser->department_id,
    //             'designation_id' => $this->currentUser->designation_id,
    //             'section_id' => $this->currentUser->section_id,
    //             'cost_center_id' => $costCenter->id,
    //             'workflow_id' => null,
    //             'created_by' => $this->currentUser->id,
    //             'draft_at' => Carbon::now(),
    //             'status' => 'Draft'
    //         ];
    //         $record = CRF::updateOrCreate(
    //             $array
    //         );
    //         if (isset($storeData['equipment_requests'])) {
    //             $equipmentRequests = [];

    //             foreach ($storeData['equipment_requests'] as $key => $request) {
    //                 $equipmentRequests[] = [
    //                     'qty' => $request['quantity'],
    //                     'state' => $request['state'],
    //                     'expense_type' => $request['expense_type'],
    //                     'expense_nature' => $request['expense_nature'],
    //                     'business_justification' => $request['business_justification'],
    //                     'equipment_id' => $request['equipment_id'],
    //                     'crf_id' => $record->id,
    //                 ];
    //             }
    //             $record->equipmentRequests()->createMany($equipmentRequests);
    //         }

    //         if (isset($storeData['software_requests'])) {
    //             foreach ($storeData['software_requests'] as $key => $request) {
    //                 $softwareRequests[] = [
    //                     'qty' => $request['quantity'],
    //                     'name' => $request['software_name'],
    //                     'version' => $request['version'],
    //                     'expense_type' => $request['expense_type'],
    //                     'expense_nature' => $request['expense_nature'],
    //                     'business_justification' => $request['business_justification'],
    //                     'crf_id' => $record->id,
    //                 ];
    //             }
    //             $record->softwareRequests()->createMany($softwareRequests);
    //         }

    //         if (isset($storeData['service_requests'])) {
    //             foreach ($storeData['service_requests'] as $key => $request) {
    //                 $serviceRequests[] = [
    //                     'name' => $request['service_name'],
    //                     'state' => $request['state'],
    //                     'expense_type' => $request['expense_type'],
    //                     'expense_nature' => $request['expense_nature'],
    //                     'business_justification' => $request['business_justification'],
    //                     'crf_id' => $record->id,
    //                 ];
    //             }
    //             $record->serviceRequests()->createMany($serviceRequests);
    //         }

    //         return Helper::sendResponse([], 'Saved as Draft', 201);
    //     } catch (\Exception $e) {
    //         \Log::error('Error in CRFService: ' . $e->getMessage());
    //         return Helper::sendError($e->getMessage(), [], 433);
    //     }
    // }
    public function updateService(array $updateData, $workflowId, $defined, $formId, $id)
    {
        $record = $this->model::findOrFail($id);
        // $costCenter = CostCenter::where('location_id', $updateData['location_id'])
        //     ->where('department_id', $this->currentUser->department_id)->first();
        // if (!$costCenter) {
        //     return Helper::sendError('Cost Center does not exist for this location and department. Please define it first.', [], 433);
        // }


        $originalStatus = $record->status;
        DB::beginTransaction();
        if (ApprovalStatus::where('form_id', $formId)->where('key', $id)->exists()) {
            ApprovalStatus::where('form_id', $formId)->where('key', $id)->delete();
        }
        $record->update([
            'request_title' => $updateData['request_title'],
            // 'cost_center_id' => $costCenter->id,
            'cost_center_id' => $updateData['cost_center_id'],
            'location_id' => $updateData['location_id'],
            'department_id' => $updateData['department_id'],
            'designation_id' => $this->currentUser->designation_id,
            'section_id' => $this->currentUser->section_id,
            'for_department' => $updateData['for_department'],
            'for_employee' => $updateData['for_employee'],
            'workflow_id' => $workflowId,
            'draft_at' => null,
            'status' => 'Pending'
        ]);
        $record->equipmentRequests()->delete();
        $record->softwareRequests()->delete();
        $record->serviceRequests()->delete();

        if (isset($updateData['equipment_requests'])) {
            $equipmentRequests = [];

            foreach ($updateData['equipment_requests'] as $key => $request) {
                $assetDetailsValue = ($originalStatus == 'Return') ? '[]' : $request['asset_details'];
                // CALCULATE TOTAL BY SUM OF EXPENSE RATE
                $assetDetailJson = json_decode($request['asset_details'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return Helper::sendError('JSON decoding error: ' . json_last_error_msg(), [], 433);
                } else {
                    $expectedExpenses = array_column($assetDetailJson, 'expected_expense');
                    $totalExpectedExpense = array_sum($expectedExpenses);
                }
                // CALCULATE TOTAL BY EXPENSE RATE

                $equipmentRequests[] = [
                    'crf_id' => $record->id,
                    'qty' => $request['quantity'],
                    'state' => $request['state'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'equipment_id' => $request['equipment_id'],
                    'amount' => ($originalStatus == 'Return') ? 0 : $request['amount'],
                    'currency_default' => $this->defaultCurrency,
                    'currency' => ($originalStatus == 'Return') ? null : $request['currency'],
                    'rate' => ($originalStatus == 'Return') ? 0 : $request['rate'],
                    // 'total' => $request['amount'] * $request['rate'],
                    'total' => ($originalStatus == 'Return') ? 0 : $totalExpectedExpense,
                    'asset_details' => $assetDetailsValue

                ];
            }
            $record->equipmentRequests()->createMany($equipmentRequests);
        }

        if (isset($updateData['software_requests'])) {
            foreach ($updateData['software_requests'] as $key => $request) {
                $assetDetailsValue = ($originalStatus == 'Return') ? '[]' : $request['asset_details'];
                // CALCULATE TOTAL BY SUM OF EXPENSE RATE
                $assetDetailJson = json_decode($request['asset_details'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return Helper::sendError('JSON decoding error: ' . json_last_error_msg(), [], 433);
                } else {
                    $expectedExpenses = array_column($assetDetailJson, 'expected_expense');
                    $totalExpectedExpense = array_sum($expectedExpenses);
                }
                // CALCULATE TOTAL BY EXPENSE RATE

                $softwareRequests[] = [
                    'crf_id' => $record->id,
                    'qty' => $request['quantity'],
                    'name' => $request['software_name'],
                    'version' => $request['version'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'amount' => ($originalStatus == 'Return') ? 0 : $request['amount'],
                    'currency_default' => $this->defaultCurrency,
                    'currency' => ($originalStatus == 'Return') ? null : $request['currency'],
                    'rate' => ($originalStatus == 'Return') ? 0 : $request['rate'],
                    // 'total' => $request['amount'] * $request['rate'],
                    'total' => ($originalStatus == 'Return') ? 0 : $totalExpectedExpense,
                    'asset_details' => $assetDetailsValue

                ];
            }
            $record->softwareRequests()->createMany($softwareRequests);
        }

        if (isset($updateData['service_requests'])) {
            foreach ($updateData['service_requests'] as $key => $request) {
                $assetDetailsValue = ($originalStatus == 'Return') ? '[]' : $request['asset_details'];
                // CALCULATE TOTAL BY SUM OF EXPENSE RATE
                $assetDetailJson = json_decode($request['asset_details'], true);
                if (json_last_error() !== JSON_ERROR_NONE) {
                    return Helper::sendError('JSON decoding error: ' . json_last_error_msg(), [], 433);;
                } else {
                    $expectedExpenses = array_column($assetDetailJson, 'expected_expense');
                    $totalExpectedExpense = array_sum($expectedExpenses);
                }
                // CALCULATE TOTAL BY EXPENSE RATE

                $serviceRequests[] = [
                    'crf_id' => $record->id,
                    'name' => $request['service_name'],
                    'state' => $request['state'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'amount' => ($originalStatus == 'Return') ? 0 : $request['amount'],
                    'currency_default' => $this->defaultCurrency,
                    'currency' => ($originalStatus == 'Return') ? null : $request['currency'],
                    'rate' => ($originalStatus == 'Return') ? 0 : $request['rate'],
                    // 'total' => $request['amount'] * $request['rate'],
                    'total' => ($originalStatus == 'Return') ? 0 : $totalExpectedExpense,
                    'asset_details' => $assetDetailsValue

                ];
            }
            $record->serviceRequests()->createMany($serviceRequests);
        }


        $result = GlobalFormService::processApprovals($record, $defined, $workflowId, $formId);
        DB::commit();

        // activity()
        // ->performedOn($record)
        // ->createdAt(now())
        // ->event('Form resubmitted after Return')
        // ->log('edited');

        $notifiedUserIds = array_unique(array_merge(
            $result['approverIds'],
            // $result['subscriberIds'],
            $result['parallelApproverIds'],
            [$result['created_by']]
        ));

        return ['notifiedUserIds' => $notifiedUserIds, 'data' => $result['resultData']];
    }

    public function draftUpdateService(array $updateData, $formId, $id)
    {
        $record = $this->model::findOrFail($id);
        // $costCenter = CostCenter::where('location_id', $updateData['location_id'])
        //     ->where('department_id', $this->currentUser->department_id)->first();
        // if (!$costCenter) {
        //     return Helper::sendError('Cost Center does not exist for this location and department. Please define it first.', [], 433);
        // }
        if (ApprovalStatus::where('form_id', $formId)->where('key', $id)->exists()) {
            ApprovalStatus::where('form_id', $formId)->where('key', $id)->delete();
        }

        $record->update(
            [
                'request_title' => $updateData['request_title'],
                'cost_center_id' => $updateData['cost_center_id'],
                'for_department' => $updateData['for_department'],
                'for_employee' => $updateData['for_employee'],
                'location_id' => $updateData['location_id'],
                'department_id' => $updateData['department_id'],
                'designation_id' => $this->currentUser->designation_id,
                'section_id' => $this->currentUser->section_id,
                'workflow_id' => null,
                'draft_at' => Carbon::now(),
                'status' => 'Draft'
            ]
        );

        $record->equipmentRequests()->delete();
        $record->softwareRequests()->delete();
        $record->serviceRequests()->delete();

        if (isset($updateData['equipment_requests'])) {
            $equipmentRequests = [];

            foreach ($updateData['equipment_requests'] as $key => $request) {
                $equipmentRequests[] = [
                    'crf_id' => $record->id,
                    'qty' => $request['quantity'],
                    'state' => $request['state'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'equipment_id' => $request['equipment_id'],
                    'amount' => $request['amount'],
                    'currency_default' => $this->defaultCurrency,
                    'currency' => $request['currency'],
                    'rate' => $request['rate'],
                    'total' => $request['amount'] * $request['rate'],
                    'asset_details' => $request['asset_details']

                ];
            }
            $record->equipmentRequests()->createMany($equipmentRequests);
        }

        if (isset($updateData['software_requests'])) {
            foreach ($updateData['software_requests'] as $key => $request) {
                $softwareRequests[] = [
                    'crf_id' => $record->id,
                    'qty' => $request['quantity'],
                    'name' => $request['software_name'],
                    'version' => $request['version'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'amount' => $request['amount'],
                    'currency_default' => $this->defaultCurrency,
                    'currency' => $request['currency'],
                    'rate' => $request['rate'],
                    'total' => $request['amount'] * $request['rate'],
                    'asset_details' => $request['asset_details']

                ];
            }
            $record->softwareRequests()->createMany($softwareRequests);
        }

        if (isset($updateData['service_requests'])) {
            foreach ($updateData['service_requests'] as $key => $request) {
                $serviceRequests[] = [
                    'crf_id' => $record->id,
                    'name' => $request['service_name'],
                    'state' => $request['state'],
                    'expense_type' => $request['expense_type'],
                    'expense_nature' => $request['expense_nature'],
                    'business_justification' => $request['business_justification'],
                    'amount' => $request['amount'],
                    'currency_default' => $this->defaultCurrency,
                    'currency' => $request['currency'],
                    'rate' => $request['rate'],
                    'total' => $request['amount'] * $request['rate'],
                    'asset_details' => $request['asset_details']

                ];
            }
            $record->serviceRequests()->createMany($serviceRequests);
        }

        // activity()
        // ->performedOn($record)
        // ->createdAt(now())
        // ->event('Draft updated')
        // ->log('edited');
        return $record;
    }
    public function destroy($id)
    {
        DB::beginTransaction();
        try {
            $data = CRF::findOrFail($id);
            if ($data->draft_at == null) {
                return Helper::sendError('Cannot process this action.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $data->delete();
            DB::commit();
            return Helper::sendResponse([], 'CRF deleted successfully', 200);
        } catch (\Exception $e) {
            DB::rollBack();
            \Log::error('Error in CRF Service: ' . $e->getMessage());
            return Helper::sendError($e->getMessage(), [], 422);
        }
    }

    public function filterRecord(Request $request)
    {
        $perPage = $request->query('perPage', 10);
        $record = CRF::with([
            'equipmentRequests:id,business_justification',
            'serviceRequests:id,name',
            'softwareRequests:id,name',
            'location:id,name',
            'costCenter:id,cost_center',
        ]);

        $filters = [
            'sequence_no',
            'request_title',
        ];

        foreach ($filters as $filter) {
            $value = $request->$filter;

            if ($value) {
                $record->where($filter, 'LIKE', '%' . $value . '%');
            }
        }

        $relationships = [
            'equipmentRequests' => 'business_justification',
            'serviceRequests' => 'name',
            'softwareRequests' => 'name',
            'location' => 'name',
            'costCenter' => 'cost_center',
        ];

        foreach ($relationships as $relationship => $column) {
            $value = $request->$relationship;

            if ($value) {
                $record->whereHas($relationship, function ($query) use ($column, $value) {
                    $query->where($column, 'LIKE', '%' . $value . '%');
                });
            }
        }

        return CRFResource::collection($record->latest()->paginate($perPage));
    }
}
