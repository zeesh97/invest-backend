<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\ConditionResource;
use App\Models\Condition;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ConditionController extends Controller
{
    public function index(Request $request)
    {
        try {
            // dd($request);
            if ($request->has('all')) {
                return Helper::sendResponse(Condition::latest()->select(['id', 'name'])->get(), 'Success');
            } else {
                return ConditionResource::collection(Condition::select('form_id')->groupBy('form_id')->get());
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_CREATED);
        }
    }
}
