<?php

namespace App\Services;

use App\Http\Helpers\Helper;
use App\Jobs\SendApprovalEmailJob;
use App\Jobs\SendQaRequestEmailJob;
use App\Models\Form;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;


class SendQaRequestService
{
    public function sendQaRequest(Request $request)
    {
        $request->validate([
            'key' => ['required', 'integer'],
            'form_id' => ['required', 'exists:forms,id'],
            'users' => ['required', 'array'],
            'users.*' => ['required', 'exists:users,id'],
        ]);
        $form = Form::findOrFail($request->form_id);
        $data = $form->identity::findOrFail($request->key);

        if ($data->task_status !== 6 || $data->task_status !== 7) {
            dispatch(new SendQaRequestEmailJob($request->key, $request->form_id, $request->users));
            return Helper::sendResponse([], 'Request has been sent successfully.', 200);
        }
        return Helper::sendError('Form task status is not closed', [], 400);
    }
}
