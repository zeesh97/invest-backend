<?php

namespace App\Http\Controllers;

use App\Enums\FormEnum;
use App\Http\Helpers\Helper;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Spatie\Activitylog\Models\Activity;

class ActivityLogController extends Controller
{
    public function getActivitiesByModel(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'form_id' => 'nullable|exists:forms,id',
            'user_id' => 'nullable|integer',
        ]);

        $modelName = 0;
        if ($request->has('form_id') && $request->filled('form_id')) {
            $modelName = FormEnum::getModelById($validated['form_id']);
        }


        $activities = Activity::query()
            ->with(['causer:id,name', 'subject:id,sequence_no,request_title'])
            ->when($request->has('user_id'), function ($query) use ($request) {
                $query->where('causer_id', $request->input('user_id'));
            })
            ->when(isset($modelName), function ($query) use ($modelName) { // Use isset for additional safety
                $query->where('subject_type', $modelName);
            })
            ->when($request->has('key'), function ($query) use ($request) {
                $query->where('subject_id', $request->input('key'));
            })
            ->when($request->has('from_date') && $request->has('to_date'), function ($query) use ($request) {
                $query->whereBetween('created_at', [$request->input('from_date'), $request->input('to_date')]);
            })
            ->latest()
            ->paginate(15);


        $activities->getCollection()->transform(function ($activity) {
            $activity->causer_name = $activity->causer->name ?? null;
            $activity->changes = $activity->changes ?? [];
            return $activity;
        });

        return response()->json($activities);
    }
}
