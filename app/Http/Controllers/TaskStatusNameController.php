<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Models\TaskStatusName;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;

class TaskStatusNameController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): JsonResponse
    {
        return Helper::sendResponse(TaskStatusName::get(), 'Success', 200);
    }
}
