<?php

namespace App\Services;

use App\Models\Team;
use Illuminate\Support\Facades\DB;
use App\Http\Helpers\Helper;
use Symfony\Component\HttpFoundation\Response;

class TeamService
{
    public function teamByFormId(int $formId)
    {
        try {
            $teams = Team::whereHas('forms', function ($query) use ($formId) {
                return $query->where('form_id', $formId); })->get(['id', 'name']);
                return $teams;

        } catch (\Exception $e) {
            return  Helper::sendError($e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }
    public function teamMembersById(int $teamId)
    {
        try {
            // dd($teamId);
            $members = Team::with('members:id,name,email','managers:id,name,email')->find($teamId);
                return $members;

        } catch (\Exception $e) {
            return  Helper::sendError($e->getMessage(), [], Response::HTTP_NOT_FOUND);
        }
    }
}
