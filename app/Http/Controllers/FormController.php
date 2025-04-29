<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Http\Requests\UpdateFormRequest;
use App\Http\Resources\FormResource;
use App\Models\Form;
use App\Models\Workflow;
use App\Models\WorkflowInitiatorField;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class FormController extends Controller
{
    public function index(Request $request)
    {
        try {
            if ($request->has('all')) {
                return Helper::sendResponse(Form::latest()->select(['id', 'name'])->get(), 'Success', 200);
            } else {
                // return FormResource::collection(Form::latest()->paginate());
                return Helper::sendResponse(Form::latest()->paginate(), 'Success', 200);
            }
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public function all()
    {
        try {
            $formTypes = Form::latest()->get(['id', 'name', 'identity']);
            return $formTypes;
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public function show(int $id): JsonResponse
    {
        try {
            $form = Form::findOrFail($id);
            return Helper::sendResponse(new FormResource($form), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list.', $e->getMessage(), Response::HTTP_NOT_FOUND);
        }
    }

    public function update(UpdateFormRequest $request, Form $form)
    {
        try {
            $result = WorkflowInitiatorField::where('form_id', $form->id)->first();
            // if ($result !== null) {
            //     return Helper::sendError('Cannot reassign the Inititator Fields, Workflow already inititated.', [], Response::HTTP_UNPROCESSABLE_ENTITY);
            // }
            $validated = $request->validated();
            if ($validated) {
                $result = $form->update([
                    'initiator_field_one_id' => $validated['initiator_field_one_id'],
                    'initiator_field_two_id' => $validated['initiator_field_two_id'],
                    'initiator_field_three_id' => $validated['initiator_field_three_id'],
                    'initiator_field_four_id' => $validated['initiator_field_four_id'],
                    'initiator_field_five_id' => $validated['initiator_field_five_id'] ?? null,
                    'callback' => $validated['callback'],
                ]);
                return Helper::sendResponse(new FormResource($form), 'Form updated successfully', 201);
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    /**
     * Remove the specified resource from storage.
     */
    // public function destroy(string $id): JsonResponse
    // {
    //     // $form = Form::findOrFail($id);
    //     // $form->delete();
    //     // return Helper::sendResponse($form, [], Response::HTTP_NO_CONTENT);
    // }

    public function getFormDetails(Request $request): JsonResponse
    {
        $validated = $this->validate($request, [
            'form_id' => 'required|exists:forms,id',
        ]);

        $form_id = $validated['form_id'];
        if ($form_id) {
            $arr = [];
            $formResource = new FormResource(Form::with(
                'initiator_field_one:id,name,identity',
                'initiator_field_two:id,name,identity',
                'initiator_field_three:id,name,identity',
                'initiator_field_four:id,name,identity',
                'initiator_field_five:id,name,identity'
            )->findOrFail($form_id));
            $arr = [];
            $form = $formResource->resource;

            if (isset($form->initiator_field_one->identity)) {
                $instance = new $form->initiator_field_one->identity;
                $arr[0] = [
                    'id' => '1',
                    'name' => $form->initiator_field_one->name,
                    'list' => $instance::select('id', 'name')->get()
                ];
            }

            if (isset($form->initiator_field_two->identity)) {
                $instance = new $form->initiator_field_two->identity;
                $arr[1] = [
                    'id' => '2',
                    'name' => $form->initiator_field_two->name,
                    'list' => $instance::select('id', 'name')->get()
                ];
            }

            if (isset($form->initiator_field_three->identity)) {
                $instance = new $form->initiator_field_three->identity;
                $arr[2] = [
                    'id' => '3',
                    'name' => $form->initiator_field_three->name,
                    'list' => $instance::select('id', 'name')->get()
                ];
            }

            if (isset($form->initiator_field_four->identity)) {
                $instance = new $form->initiator_field_four->identity;
                $arr[3] = [
                    'id' => '4',
                    'name' => $form->initiator_field_four->name,
                    'list' => $instance::select('id', 'name')->get()
                ];
            }
            if (isset($form->initiator_field_five->identity)) {
                $instance = new $form->initiator_field_five->identity;
                $arr[4] = [
                    'id' => '5',
                    'name' => $form->initiator_field_five->name,
                    'list' => $instance::select('id', 'name')->get()
                ];
            }

            return Helper::sendResponse($arr, 'Success', 200);
        } else {
            return Helper::sendError('Form not found', [], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
