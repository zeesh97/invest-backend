<?php

namespace App\Listeners;

use App\Events\AssignPermissionToUsers;
use App\Models\UserAccessLevel;
use DB;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;

class AssignPermissionToUsersListener
{
    /**
     * Handle the event.
     */
    public function handle(AssignPermissionToUsers $event): void
    {
        $userIds = $event->users;
        $model = is_object($event->model) ? get_class($event->model) : $event->model;
        $key = $event->key;

        $existingAccesses = DB::table('user_access_levels')
            ->whereIn('user_id', $userIds)
            ->where('accessible_type', $model)
            ->where('accessible_id', $key)
            ->pluck('user_id')
            ->toArray();

            $newUserIds = array_diff($userIds, $existingAccesses);

        $accessData = [];
        foreach ($newUserIds as $id) {
            $accessData[] = [
                'user_id' => (int) $id,
                'accessible_type' => $model,
                'accessible_id' => (int) $key,
            ];
        }

        if (!empty($accessData)) {
            DB::transaction(function () use ($accessData) {
                UserAccessLevel::insertOrIgnore($accessData);
            });
        }
    }
}
