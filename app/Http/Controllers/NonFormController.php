<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Http\Requests\UpdateFormRequest;
use App\Http\Resources\FormResource;
use App\Models\Form;
use App\Models\NonForm;
use App\Models\Workflow;
use App\Models\WorkflowInitiatorField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class NonFormController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(NonForm::latest()->select(['id', 'name'])->get(), 'Success', 200);
            } else {
                return Helper::sendResponse(NonForm::latest()->paginate(), 'Success', 200);
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public function all()
    {
        try {
            $formTypes = NonForm::latest()->get(['id', 'name', 'identity']);
            return $formTypes;
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

}
