<?php

namespace App\Http\Helpers;

use App\Models\Form;
use App\Models\SetupField;
use App\Models\Subscription;
use App\Models\WorkflowInitiatorField;
use DB;
use Exception;
use Illuminate\Pagination\LengthAwarePaginator;
use Log;
use Symfony\Component\HttpFoundation\Response;

class Helper
{
    public static function sendResponse($result, $message = '', $status = 200)
    {
        if ($result instanceof LengthAwarePaginator) {
            $response['data'] = $result->toArray();
        } else {
            $response['data'] = $result;
        }
        $response = [
            'success' => true,
            'data'    => $response['data'],
            'message' => $message,
        ];
        return response()->json($response, $status)
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }

    // public static function sendError($message, $errors=[], $status)
    // {
    //     $response = ['success' => false, 'message'=> $message];

    //     if(!empty($errors)){
    //         $response['data'] = $errors;
    //     }
    //     return response()->json($response, $status);
    // }
    public static function sendError($message, $errors = [], $status = 400, $code = 'general_error')
    {
        $response = [
            'success' => false,
            'message' => $message,
            'code' => $code,
        ];

        if (!empty($errors)) {
            $response['errors'] = $errors;
        }

        return response()->json($response, $status);
    }

    public static function appTimezone()
    {
        return cache()->remember('app_timezone', now()->addMinutes(30), function () {
            return \App\Models\Setting::whereNotNull('timezone')->value('timezone') ?? 'UTC';
        });
    }


    public static function getModelById($modelName, $modelKey)
    {
        try {
            $model = $modelName::findOrFail($modelKey);
            return $model;
        } catch (\Illuminate\Database\Eloquent\ModelNotFoundException $e) {
            return self::sendError($e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public static function workflowInitiatorFieldsByKey(int $workflowId, string $columnName, $initiatior_field)
    {
        if ($columnName == "initiatior_field_one") {
            $key = WorkflowInitiatorField::where('workflow_id', $workflowId)->value('key_one');
            $model = $initiatior_field;
            $result = $model::where('id', $key)->select('id', 'name')->first();
            return $result;
        }
        if ($columnName == "initiatior_field_two") {
            $key = WorkflowInitiatorField::where('workflow_id', $workflowId)->value('key_two');
            $model = $initiatior_field;
            $result = $model::where('id', $key)->select('id', 'name')->first();
            return $result;
        }
        if ($columnName == "initiatior_field_three") {
            $key = WorkflowInitiatorField::where('workflow_id', $workflowId)->value('key_three');
            $model = $initiatior_field;
            $result = $model::where('id', $key)->select('id', 'name')->first();
            return $result;
        }
        if ($columnName == "initiatior_field_four") {
            $key = WorkflowInitiatorField::where('workflow_id', $workflowId)->value('key_four');
            $model = $initiatior_field;
            $result = $model::where('id', $key)->select('id', 'name')->first();
            return $result;
        }
        if ($columnName == "initiatior_field_five") {
            $key = WorkflowInitiatorField::where('workflow_id', $workflowId)->value('key_five');
            $model = $initiatior_field;
            $result = $model::where('id', $key)->select('id', 'name')->first();
            return $result;
        }
    }

    public static function find_initiator_field($form_id)
    {
        $form = Form::where('id', $form_id)
            ->whereNotNull('initiator_field_one_id')
            ->whereNotNull('initiator_field_two_id')
            ->whereNotNull('initiator_field_three_id')
            ->whereNotNull('initiator_field_four_id')
            ->whereNotNull('initiator_field_five_id')->first();
        if (!empty($form)) {
            $initiator_field_one = SetupField::findOrFail($form->initiator_field_one_id);
            $initiator_field_two = SetupField::findOrFail($form->initiator_field_two_id);
            $initiator_field_three = SetupField::findOrFail($form->initiator_field_three_id);
            $initiator_field_four = SetupField::findOrFail($form->initiator_field_four_id);
            $initiator_field_five = SetupField::findOrFail($form->initiator_field_five_id);
            $data = [
                'initiator_field_one' => $initiator_field_one,
                'initiator_field_two' => $initiator_field_two,
                'initiator_field_three' => $initiator_field_three,
                'initiator_field_four' => $initiator_field_four,
                'initiator_field_five' => $initiator_field_five,
            ];

            return Helper::sendResponse($data, 'Success');
        } else {
            return Helper::sendError("Please define Initiator Fields for this form", [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
