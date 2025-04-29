<?php

namespace App\Traits;

use App\Enums\FormEnum;
use App\Http\Helpers\Helper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

trait SearchTitleByFormIdTrait
{
    // public function searchTitleByFormId(Request $request): JsonResponse
    //     {
    //         $validated = $request->validate([
    //             'search' => 'required|string|max:150',
    //             'form_id' => 'required|integer|exists:forms,id'
    //         ]);
    //         $search = $validated['search'];
    //         $formId = $validated['form_id'];
    //         $form = FormEnum::getModelById($formId);
    //         $form = new $form;
    //         $table = $form->getTable();
    //         return Helper::sendResponse(DB::table($table)
    //             ->select('request_title')
    //             ->where('request_title', 'like', '%' . $search . '%')
    //             ->limit(5)
    //             ->get(), 'Success', 200);
    //     }

    public function searchTitleByFormId(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'search' => 'required|string|max:150',
            'form_id' => 'required|integer|exists:forms,id',
        ]);

        $search = trim(strip_tags($validated['search']));
        $formId = $validated['form_id'];
        $form = FormEnum::getModelById($formId);
        $form = new $form;
        $table = $form->getTable();
        return Helper::sendResponse($results = DB::table($table)
        ->select(['request_title', 'sequence_no', 'id'])
        ->where(function ($query) use ($search) {
            $query->where('request_title', 'like', '%' . $search . '%')
                  ->orWhere('sequence_no', 'like', '%' . $search . '%');
        })
        ->limit(5)
        ->get(), 'Success', 200);
    }
}
