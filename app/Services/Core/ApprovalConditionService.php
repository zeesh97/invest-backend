<?php

namespace App\Services\Core;

use App\Models\ApprovalStatus;
use App\Models\SoftwareCategory;
use Illuminate\Support\Facades\Auth;

class ApprovalConditionService
{
    public function __invoke()
    {
        return app(ApprovalConditionService::class);
    }
    public function approvalConditions($data, $formId, $conditionId, $sequence_no, $approver_id): bool
    {
        switch ($conditionId) {
            case 1:
                return ($data->change_significance == 'Major') ? true : false;
            case 2:
                return ($data->change_significance == 'Minor') ? true : false;
            case 3:
                return ($data->location_id == 2) ? true : false;
            case 4:
                return $data && $data->created_at >= '2024-01-01' && $data->created_at <= '2024-12-31';
            case 5:
                $equipmentTotal = $data->equipmentRequests->sum('total');
                $softwareTotal = $data->softwareRequests->sum('total');
                $serviceTotal = $data->serviceRequests->sum('total');
                $totalSum = $equipmentTotal + $softwareTotal + $serviceTotal;

                if ($totalSum >= 1000000 && $totalSum <= 2000000) {
                    return true;
                }
                return false;
            case 6:
                $equipmentTotal = $data->equipmentRequests->sum('total');
                $softwareTotal = $data->softwareRequests->sum('total');
                $serviceTotal = $data->serviceRequests->sum('total');
                $totalSum = $equipmentTotal + $softwareTotal + $serviceTotal;

                if ($totalSum >= 1 && $totalSum < 1000000) {
                    return true;
                }
                return false;
            case 7:
                return ! $data->equipmentRequests()->where('expense_nature', '!=', 1)->exists() &&
                    ! $data->softwareRequests()->where('expense_nature', '!=', 1)->exists() &&
                    ! $data->serviceRequests()->where('expense_nature', '!=', 1)->exists();

            case 8:
                return ! $data->equipmentRequests()->where('expense_nature', '!=', 2)->exists() &&
                    ! $data->softwareRequests()->where('expense_nature', '!=', 2)->exists() &&
                    ! $data->serviceRequests()->where('expense_nature', '!=', 2)->exists();
            case 9:
                if (!ApprovalStatus::where('form_id', 4)
                    ->where('key', $data->id)->where('user_id', Auth::user()->id)->where('condition_id', 9)->exists()) {
                    return true;
                }
                if (
                    $data->status == 'Pending' &&
                    $data->equipmentRequests()?->whereNull('asset_details')->exists() &&
                    $data->softwareRequests()?->whereNull('asset_details')->exists() &&
                    $data->serviceRequests()?->whereNull('asset_details')->exists()
                ) {
                    return true;
                }
                $hasPurchased = $data->equipmentRequests()
                    ->whereJsonContains('asset_details', ['action' => 'Purchase'])->exists()
                    || $data->softwareRequests()
                    ->whereJsonContains('asset_details', ['action' => 'Purchase'])->exists()
                    || $data->serviceRequests()
                    ->whereJsonContains('asset_details', ['action' => 'Purchase'])->exists();

                if (!$hasPurchased) {
                    ApprovalStatus::where('form_id', 4)
                        ->where('key', $data->id)
                        ->where('status', '<>', 'Approved')
                        ->update([
                            'status' => 'Approved',
                            'status_at' => now(),
                            'responded_by' => Auth::user()->id,
                            'reason' => 'Action: Purchase'
                        ]);
                    $data->update(['status' => 'Approved']);
                }
                return true;
            case 10:
                $category = SoftwareCategory::where('name', 'Sap Internal')->first();
                if (!$category) {
                    return false;
                }
                return ($data->software_category_id == $category->id) ? true : false;
            case 11:
                $category = SoftwareCategory::where('name', 'Sap Sales Group')->first();
                if (!$category) {
                    return false;
                }
                return ($data->software_category_id == $category->id) ? true : false;
            // case 12:
            //     $updatedCount = ApprovalStatus::where('form_id', $formId)
            //         ->where('key', $data->id)
            //         ->where('approver_id', $approver_id)
            //         ->where('sequence_no', $sequence_no)
            //         ->update(['status' => 'Approved']);
            //     return $updatedCount > 0;
                // return ($data->software_category_id == $category->id) ? true : false;
            default:
                return false;
        }
    }
}
