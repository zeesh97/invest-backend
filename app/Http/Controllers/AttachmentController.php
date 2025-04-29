<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Requests\AttachmentRequest;
use App\Http\Resources\AttachmentResource;
use App\Models\Attachment;
use App\Models\Form;
use App\Services\AttachmentService;
use Auth;
use Illuminate\Http\Request;
use Storage;

class AttachmentController extends Controller
{
    protected $attachments;

    public function __construct(AttachmentService $attachments)
    {
        $this->attachments = $attachments;
    }
    public function store(AttachmentRequest $request)
    {
        try {
            $validated = $request->validated();
            if ($validated['attachments']) {
                $form = $request->getForm();
                $attachments = $this->attachments->storeAttachment($request->attachments, $request->key, $form->identity);

                if($attachments)
                {
                    $result = $form->identity::find($request->key)->attachments()->createMany($attachments);
                    return Helper::sendResponse(AttachmentResource::collection($result), 'Attachment created successfully', 201);
                }
                return $attachments;
            }
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), [], 403);
        }
    }

    public function download(Request $request, $id)
    {
        // Check if user is authenticated
        if (!Auth::check()) {
            return Helper::sendError('Unauthorized', [], 401);
        }

        $attachment = Attachment::findOrFail($id);

        // Serve the file
        return Storage::disk('public')->download($attachment->filename);
    }

    public function destroy($id)
    {
        try {
             $attachment = Attachment::findOrFail($id);
            $attachmentPath = str_replace('uploads/', '', $attachment->filename);

            if (Storage::disk('public')->exists($attachmentPath)) {
               Storage::disk('public')->delete($attachmentPath);
            }
            $attachment->delete();

            return Helper::sendResponse([], 'Attachment deleted successfully', 200);
        } catch (\Exception $e) {
            \Log::error('Attachment Deletion Failed: ' . $e->getMessage());
            return Helper::sendError($e->getMessage(), [], 403);
        }
    }
}
