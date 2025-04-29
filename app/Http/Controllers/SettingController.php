<?php

namespace App\Http\Controllers;

use App\Http\Helpers\Helper;
use App\Http\Requests\UpdateSettingRequest;
use App\Models\Setting;
use App\Services\SettingService;
use Hash;
use Illuminate\Http\Request;

class SettingController extends Controller
{
    private $settingService;

    public function __construct(SettingService $settingService)
    {
        $this->settingService = $settingService;
    }
    public function index()
    {
        $list = Setting::get();
        return Helper::sendResponse($list, 'Success', 200);
    }
    public function update(UpdateSettingRequest $request)
    {
        try {
            // dd($this->settingService);

            // if (!auth()->user()->hasRole('admin')) {
            //     return Helper::sendError('Unauthorized', [], 401);
            // }
            $validated = $request->validated();
            // $validated['email_password'] = $validated['email_password'];
            // $validated[timezone_identifiers_list()[$request->input('timezone', 'UTC')]];
            $this->settingService->update($validated);

            return Helper::sendResponse([], "Settings updated successfully", 201);
        } catch (\Exception $e) {
            return Helper::sendError($e->getMessage(), "An error occurred while updating settings", 500);
        }
    }
}
