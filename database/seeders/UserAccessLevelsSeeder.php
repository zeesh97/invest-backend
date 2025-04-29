<?php

namespace Database\Seeders;

use App\Models\Form;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Collection;

class UserAccessLevelsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        if (!Form::find(1)) {
            set_time_limit(300); // Increase execution time to 5 minutes

            $formIdToType = [
                1 => 'App\Models\Forms\QualityAssurance',
                2 => 'App\Models\Forms\SCRF',
                3 => 'App\Models\Forms\Deployment',
                4 => 'App\Models\Forms\CRF',
                5 => 'App\Models\Forms\MobileRequisition',
                6 => 'App\Models\Forms\MasterDataManagementForm',
            ];

            DB::table('approval_statuses')
                ->whereIn('form_id', array_keys($formIdToType))
                ->orderBy('user_id')
                ->orderBy('form_id')
                ->chunk(300, function ($records) use ($formIdToType) {
                    $insertData = [];

                    foreach ($records as $item) {
                        // Determine the accessible_type using form_id
                        $accessibleType = $formIdToType[$item->form_id] ?? null;

                        if ($accessibleType) {
                            // Check if the combination of user_id, accessible_type, and accessible_id already exists
                            $exists = DB::table('user_access_levels')
                                ->where('user_id', $item->user_id)
                                ->where('accessible_type', $accessibleType)
                                ->where('accessible_id', $item->key)
                                ->exists();

                            if (!$exists) {
                                // Prepare data for insertion
                                $insertData[] = [
                                    'user_id' => $item->user_id,
                                    'accessible_type' => $accessibleType,
                                    'accessible_id' => $item->key,
                                ];
                            }
                        }
                    }

                    // Insert data into user_access_levels if any new records were found
                    if (!empty($insertData)) {
                        DB::table('user_access_levels')->insert($insertData);
                    }
                });
            DB::table('scrf')
                ->select('id', 'created_by') // Select only the necessary fields
                ->orderBy('id') // Chunking requires a consistent order
                ->chunk(300, function ($records) use ($formIdToType) {
                    $insertData = [];

                    foreach ($records as $item) {
                        $accessibleType = $formIdToType[2]; // Since we're dealing with SCRF form, set the accessible type

                        // Check if the combination of user_id (created_by), accessible_type, and accessible_id already exists
                        $exists = DB::table('user_access_levels')
                            ->where('user_id', $item->created_by)
                            ->where('accessible_type', $accessibleType)
                            ->where('accessible_id', $item->id) // `id` from the `scrf` table becomes `accessible_id`
                            ->exists();

                        if (!$exists) {
                            // Prepare data for insertion
                            $insertData[] = [
                                'user_id' => $item->created_by,
                                'accessible_type' => $accessibleType,
                                'accessible_id' => $item->id,
                            ];
                        }
                    }

                    // Insert the data into user_access_levels if any new records were found
                    if (!empty($insertData)) {
                        DB::table('user_access_levels')->insert($insertData);
                    }
                });
            DB::table('crfs')
                ->select('id', 'created_by') // Select only the necessary fields
                ->orderBy('id') // Chunking requires a consistent order
                ->chunk(300, function ($records) use ($formIdToType) {
                    $insertData = [];

                    foreach ($records as $item) {
                        $accessibleType = $formIdToType[4]; // Since we're dealing with SCRF form, set the accessible type

                        // Check if the combination of user_id (created_by), accessible_type, and accessible_id already exists
                        $exists = DB::table('user_access_levels')
                            ->where('user_id', $item->created_by)
                            ->where('accessible_type', $accessibleType)
                            ->where('accessible_id', $item->id) // `id` from the `scrf` table becomes `accessible_id`
                            ->exists();

                        if (!$exists) {
                            // Prepare data for insertion
                            $insertData[] = [
                                'user_id' => $item->created_by,
                                'accessible_type' => $accessibleType,
                                'accessible_id' => $item->id,
                            ];
                        }
                    }

                    // Insert the data into user_access_levels if any new records were found
                    if (!empty($insertData)) {
                        DB::table('user_access_levels')->insert($insertData);
                    }
                });


            DB::table('mobile_requisitions')
                ->select('id', 'created_by') // Select only the necessary fields
                ->orderBy('id') // Chunking requires a consistent order
                ->chunk(300, function ($records) use ($formIdToType) {
                    $insertData = [];

                    foreach ($records as $item) {
                        $accessibleType = $formIdToType[5]; // Since we're dealing with SCRF form, set the accessible type

                        // Check if the combination of user_id (created_by), accessible_type, and accessible_id already exists
                        $exists = DB::table('user_access_levels')
                            ->where('user_id', $item->created_by)
                            ->where('accessible_type', $accessibleType)
                            ->where('accessible_id', $item->id) // `id` from the `scrf` table becomes `accessible_id`
                            ->exists();

                        if (!$exists) {
                            // Prepare data for insertion
                            $insertData[] = [
                                'user_id' => $item->created_by,
                                'accessible_type' => $accessibleType,
                                'accessible_id' => $item->id,
                            ];
                        }
                    }

                    // Insert the data into user_access_levels if any new records were found
                    if (!empty($insertData)) {
                        DB::table('user_access_levels')->insert($insertData);
                    }
                });


            DB::table('assign_tasks')
                ->join('assign_task_team', 'assign_tasks.id', '=', 'assign_task_team.assign_task_id')
                ->select('assign_tasks.assignable_type', 'assign_tasks.assignable_id', 'assign_task_team.member_id as user_id') // Select necessary columns and alias member_id as user_id
                ->chunk(100, function ($tasks) {
                    $dataToInsert = [];

                    foreach ($tasks as $task) {
                        // Check if the combination already exists
                        $exists = DB::table('user_access_levels')
                            ->where('user_id', $task->user_id)
                            ->where('accessible_type', $task->assignable_type)
                            ->where('accessible_id', $task->assignable_id)
                            ->exists();

                        if (!$exists) {
                            $dataToInsert[] = [
                                'user_id' => $task->user_id,
                                'accessible_type' => $task->assignable_type,
                                'accessible_id' => $task->assignable_id,
                            ];
                        }
                    }

                    if (!empty($dataToInsert)) {
                        DB::table('user_access_levels')->insert($dataToInsert);
                    }
                });
        }
    }
}
