<?php

namespace App\Http\Controllers\Forms;

use App\Http\Controllers\Controller;
use App\Http\Helpers\Helper;
use App\Http\Requests\CommentStoreRequest;
use App\Http\Requests\NonCommentStoreRequest;
use App\Http\Resources\CommentResource;
use App\Jobs\AttachmentsJob;
use App\Jobs\MentionedInCommentJob;
use App\Models\Comment;
use App\Models\Form;
use App\Models\Forms\SCRF;
use App\Models\NonForm;
use App\Models\RequestSupportForm;
use App\Models\User;
use App\Services\AttachmentService;
use Illuminate\Http\Request;
use Illuminate\Support\Carbon;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class CommentController extends Controller
{

    public function index(Request $request)
    {
        try {
            $validated = $request->validate([
                'form_id' => 'required|integer|exists:forms,id',
                'key' => 'required|integer',
            ]);
            $form = Form::findOrFail($validated['form_id']);

            if (!$form) {
                return Helper::sendError('Record Not Found', [], 404);
            }

            // dd($form->identity);
            $comments = Comment::where('commentable_id', $validated['key'])
                ->where('commentable_type', $form->identity)
                ->get();
            return Helper::sendResponse(CommentResource::collection($comments), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }

    public function indexNonForm(Request $request)
    {
        try {
            $validated = $request->validate([
                'non_form_id' => 'required|integer|exists:non_forms,id',
                'key' => 'required|integer',
            ]);
            $non_form = NonForm::findOrFail($validated['non_form_id']);

            if (!$non_form) {
                return Helper::sendError('Record Not Found', [], 404);
            }

            // dd($form->identity);
            $comments = Comment::where('commentable_id', $validated['key'])
                ->where('commentable_type', $non_form->identity)
                ->get();
            return Helper::sendResponse(CommentResource::collection($comments), 'Success', 200);
        } catch (\Exception $e) {
            return Helper::sendError('Failed to fetch list: ' . $e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }
    public function store(CommentStoreRequest $request)
    {
        try {
            $validated = $request->validated();
            $findForm = Form::findOrFail($validated['form_id']);
            $instance = new Comment();
            $instance->comment = $validated['comment'];
            $instance->user_id = auth()->user()->id;
            $instance->commentable_id = $validated['key'];
            $instance->commentable_type = $findForm->identity;
            $instance->created_at = Carbon::now();
            $instance->save();
            $attachments = [];

            if (isset($request->attachments) && count($request->attachments) > 0) {
                $attachments = new AttachmentService();
                $attachments = $attachments->storeAttachment($request->attachments, $instance->id, Comment::class);
                $instance->attachments()->createMany($attachments);
            }
            if (!empty($request['mentioned_user_ids'])) {
                // $mentioned_user_ids = $request['mentioned_user_ids'];
                $mentionedUserIds = json_decode($request['mentioned_user_ids'], true);
                // $mentioned_users = User::whereIn('id', $mentioned_user_ids)->get(['id', 'name', 'email']);

                dispatch(new MentionedInCommentJob($mentionedUserIds, $findForm->identity, $validated['key'], $findForm->name, $findForm->slug));
            }

            return Helper::sendResponse(new CommentResource($instance), 'Success', 201);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function storeNonForm(NonCommentStoreRequest $request)
    {
        try {
            $validated = $request->validated();
            $findForm = NonForm::findOrFail($validated['non_form_id']);
            $instance = new Comment();
            $instance->comment = $validated['comment'];
            $instance->user_id = auth()->user()->id;
            $instance->commentable_id = $validated['key'];
            $instance->commentable_type = $findForm->identity;
            $instance->created_at = Carbon::now();
            $instance->save();
            $attachments = [];

            if (isset($request->attachments) && count($request->attachments) > 0) {
                $attachments = new AttachmentService();
                $attachments = $attachments->storeAttachment($request->attachments, $instance->id, Comment::class);
                $instance->attachments()->createMany($attachments);
            }
            if (!empty($request['mentioned_user_ids'])) {
                // $mentioned_user_ids = $request['mentioned_user_ids'];
                $mentionedUserIds = json_decode($request['mentioned_user_ids'], true);
                // $mentioned_users = User::whereIn('id', $mentioned_user_ids)->get(['id', 'name', 'email']);

                dispatch(new MentionedInCommentJob($mentionedUserIds, $findForm->identity, $validated['key'], $findForm->name, $findForm->slug));
            }

            return Helper::sendResponse(new CommentResource($instance), 'Success', 201);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }


    public function enableDisableComments(Request $request)
    {
        try {
            $validated = $request->validate([
                'form_id' => 'required|integer|exists:forms,id',
                'key' => 'required|integer',
                'status' => 'required|boolean',
            ]);
            $form = Form::findOrFail($validated['form_id']);
            $formDetail = $form->identity::findOrFail($validated['key']);
            if($formDetail)
            {
               $response = $formDetail->update([
                    'comment_status' => $validated['status']
                ]);
            }
            return Helper::sendResponse($response, 'Success', 200);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }

    public function enableDisableCommentsNonForm(Request $request)
    {
        try {
            $validated = $request->validate([
                'non_form_id' => ['required', 'exists:non_forms,id'],
                'key' => 'required|integer',
                'status' => 'required|boolean',
            ]);
            $form = NonForm::findOrFail($validated['non_form_id']);
            $formDetail = $form->identity::findOrFail($validated['key']);
            if($formDetail)
            {
               $response = $formDetail->update([
                    'comment_status' => $validated['status']
                ]);
            }
            return Helper::sendResponse($response, 'Success', 200);
        } catch (\Exception $exception) {
            return response()->json(['message' => $exception->getMessage()], Response::HTTP_UNPROCESSABLE_ENTITY);
        }
    }
}
