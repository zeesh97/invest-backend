<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Models\EmailLog;
use Illuminate\Http\Request;

class EmailLogController extends Controller
{
    public function index()
    {
        return Helper::sendResponse(EmailLog::latest()->paginate(), 'Success', 200);
    }

    public function store(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'recipient' => 'required|recipient',
            'subject' => 'required|subject',
            'body' => 'required|body',
            'status' => 'required|status',
        ]);
        $emailLog = EmailLog::create([
            'recipient' => $request->recipient,
            'subject' => $request->subject,
            'body' => $request->body,
            'status' => 'queued',
        ]);

        return $emailLog;
    }
}
