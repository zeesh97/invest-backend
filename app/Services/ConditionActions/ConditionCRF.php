<?php

namespace App\Services\ConditionActions;

use App\Enums\FormEnum;
use App\Models\ApprovalStatus;
use App\Models\Scopes\FormDataAccessScope;
use DB;

class ConditionCRF
{
    public static function execute(int $formId, $data, int $conditionId): bool
    {
        if ($formId === 4 && $conditionId === 8) {
            $data->load([
                'equipmentRequests',
                'softwareRequests',
                'serviceRequests'
            ]);
            if (!$data) {
                return false;
            }
            collect(['equipmentRequests', 'softwareRequests', 'serviceRequests'])
            ->contains(function ($relation) use ($data, $formId) {

                return $data->{$relation}->contains(function ($request) use ($formId, $data) {
                        // dd($formId, $request->asset_details, $data, $request->expense_nature);
                        if (
                            $request->expense_nature == 2 &&
                            self::hasLowExpenseInAssets($request->asset_details)
                        ) {
                            return self::applyConditionAndDelete($formId, $data->id);
                        }
                    });
                });
        }
        return true;
    }

    protected static function hasLowExpenseInAssets($assetDetails): bool
    {

        $details = json_decode($assetDetails, true);
        if (empty($assetDetails) || is_null($assetDetails) || $assetDetails == '[]') return true;

        if (json_last_error() !== JSON_ERROR_NONE || !is_array($details)) {
            return false;
        }

        foreach ($details as $detail) {
            $expectedExpense = $detail['expected_expense'] ?? null;
            if (is_numeric($expectedExpense) && (float)$expectedExpense >= 500000) {
                return false;
            }
        }

        return true;
    }

    public static function applyConditionAndDelete(int $formId, int $key)
    {

        $deletedCount = ApprovalStatus::where('form_id', $formId)
        ->where('key', $key)
        ->where('status', 'Pending')
        ->where('condition_id', 8)
        ->delete();

        return $deletedCount > 0 ;
    }
}
