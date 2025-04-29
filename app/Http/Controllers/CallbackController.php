<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Resources\CallbackResource;
use App\Models\Callback;
use App\Models\Section;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CallbackController extends Controller
{
    public function __construct()
    {
        if (request()->is('api/*')) {
            $this->middleware('auth:sanctum');
            $this->middleware('role_or_permission:Callback-view|Callback-create|Callback-edit|Callback-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Callback-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Callback-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Callback-delete', ['only' => ['destroy']]);
        } else {
            $this->middleware('role_or_permission:Callback-view|Callback-create|Callback-edit|Callback-delete', ['only' => ['index', 'show']]);
            $this->middleware('role_or_permission:Callback-create', ['only' => ['create', 'store']]);
            $this->middleware('role_or_permission:Callback-edit', ['only' => ['edit', 'update']]);
            $this->middleware('role_or_permission:Callback-delete', ['only' => ['destroy']]);
        }
    }

    // public function index(Request $request)
    // {
    //     try {
    //         if ($request->has('all')) {
    //             return Helper::sendResponse(Callback::latest()->select(['id', 'name'])->get(), 'Success');
    //         } else {
    //             return CallbackResource::collection(Callback::latest()->paginate());
    //         }
    //     } catch (\Exception $e) {
    //         return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
    //     }
    // }
    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                $records = Callback::latest()->select(['id', 'name', 'url'])->get();
                return Helper::sendResponse($records, 'Success', 200);
            } else {
                $perPage = $request->get('per_page', 10);
                $records = Callback::select(['id', 'name', 'url'])
                    ->latest()
                    ->paginate($perPage);
                return $records;
            }
        } catch (\Exception $e) {
            \Log::error('Failed to fetch cost centers: ' . $e->getMessage(), [
                'exception' => $e,
            ]);
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_INTERNAL_SERVER_ERROR);
        }
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResponse
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:100',
                'url' => 'nullable|string|max:100'
            ]);
            $callback = Callback::create($validated);
            return Helper::sendResponse($callback, 'Callback created successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $callback = Callback::findOrFail($id);
            return Helper::sendResponse(new CallbackResource($callback), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(Request $request, Callback $callback)
    {
        try {
            $validated = $request->validate([
                'name' => 'nullable|string|max:100',
                'url' => 'nullable|string|max:100',
            ]);

            $callback->update($validated);

            return Helper::sendResponse($callback, 'Callback updated successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function destroy(int $id): JsonResponse
    {
        try {
            $callback = Callback::find($id);

            if (!$callback) {
                return Helper::sendError("No record found.", [], Response::HTTP_UNPROCESSABLE_ENTITY);
            }
            $callback->delete();
            return Helper::sendResponse($callback, 'Callback deleted successfully');
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
