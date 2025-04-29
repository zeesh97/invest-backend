<?php

namespace App\Services;

use App\Http\Helpers\Helper;
use App\Models\Form;
use App\Models\WorkflowInitiatorField;
use Auth;

class GlobalFormStoreService
{
    public function workflowCheck($validated, $modelId, $workflowUser = null)
    {
        $workflowUser = $workflowUser ?? Auth::user();
        $form = Form::where('id', $modelId)
            ->where(function ($query) {
                $query->whereNotNull('initiator_field_one_id')
                    ->orWhereNotNull('initiator_field_two_id')
                    ->orWhereNotNull('initiator_field_three_id')
                    ->orWhereNotNull('initiator_field_four_id')
                    ->orWhereNotNull('initiator_field_five_id');
            })
            ->exists();
        if (!$form) {
            return Helper::sendError("Please first assign Initiator Fields then Workflow for this form.", [], 422);
        }

        $form = Form::find($modelId);

        $workflowDefined = WorkflowInitiatorField::with(
            'workflow.workflowSubscribersApprovers',
            'initiator_field_one',
            'initiator_field_two',
            'initiator_field_three',
            'initiator_field_four',
            'initiator_field_five'
        )
            ->where('initiator_id', $workflowUser->id)
            ->where('form_id', $modelId);

            if ($form->initiator_field_one_id) {
                $workflowDefined->where('initiator_field_one_id', $form->initiator_field_one_id);
            }
            if ($form->initiator_field_two_id) {
                $workflowDefined->where('initiator_field_two_id', $form->initiator_field_two_id);
            }
            if ($form->initiator_field_three_id) {
                $workflowDefined->where('initiator_field_three_id', $form->initiator_field_three_id);
            }
            if ($form->initiator_field_four_id) {
                $workflowDefined->where('initiator_field_four_id', $form->initiator_field_four_id);
            }
            if ($form->initiator_field_five_id) {
                $workflowDefined->where('initiator_field_five_id', $form->initiator_field_five_id);
            }
            $workflowDefined = $workflowDefined->get();

        // dd($workflowDefined);
        $allInitName = [];
        foreach ($workflowDefined as $values) {
            $workflowId = ['workflow_id' => $values->workflow_id];
            $initName = [];

            if ($values->key_one !== null) {
                $initName[$values->initiator_field_one->name] = $values->key_one;
            }
            if ($values->key_two !== null) {
                $initName[$values->initiator_field_two->name] = $values->key_two;
            }
            if ($values->key_three !== null) {
                $initName[$values->initiator_field_three->name] = $values->key_three;
            }
            if ($values->key_four !== null) {
                $initName[$values->initiator_field_four->name] = $values->key_four;
            }
            if ($values->key_five !== null) {
                $initName[$values->initiator_field_five->name] = $values->key_five;
            }

            $allInitName[] = $initName;
            $workflowIds[] = $workflowId;
        }

        if (empty($allInitName)) {
            return Helper::sendError("Workflow is not defined for you.", [], 422);
        }

        $softwareSubcategories = $validated['software_subcategory_id'] ?? null;

        $combinations = [];
        if (
            array_key_exists('Software Subcategory', $allInitName[0])
            && $softwareSubcategories
            && array_key_exists('Software Category', $allInitName[0])
        ) {
            foreach ($softwareSubcategories as $softwareSubcategory) {
                $combinations[] = [
                    'Location' => array_key_exists('Location', $allInitName[0])
                        ? (int) ($validated['location_id'] ? $validated['location_id'] : $workflowUser->location_id) : null,
                    'Department' => array_key_exists('Department', $allInitName[0])
                        ? (int) ($validated['department_id'] ? $validated['department_id'] : $workflowUser->department_id) : null,
                    'Section' => array_key_exists('Section', $allInitName[0])
                        ? (int) ($validated['section_id'] ? $validated['section_id'] : $workflowUser->section_id) : null,
                    'Designation' => array_key_exists('Designation', $allInitName[0])
                        ? (int) ($validated['designation_id'] ? $validated['designation_id'] : $workflowUser->designation_id) : null,
                    'Software Category' => array_key_exists('Software Category', $allInitName[0])
                        ? (int) $validated['software_category_id'] : null,
                    'Project' => array_key_exists('Project', $allInitName[0])
                        ? (int) $validated['project_id'] : null,
                    'Project MDM' => array_key_exists('Project MDM', $allInitName[0])
                        ? (int) $validated['mdm_project_id'] : null,
                    'Form' => array_key_exists('Form', $allInitName[0])
                        ? (int) $validated['form_id'] : null,
                    'Software Subcategory' => (int) $softwareSubcategory,
                ];
            }
        } else {
            $combinations[] = [
                'Location' => array_key_exists('Location', $allInitName[0])
                    ? (int) $workflowUser->location_id : null,
                'Department' => array_key_exists('Department', $allInitName[0])
                    ? $workflowUser->department_id : null,
                'Section' => array_key_exists('Section', $allInitName[0])
                    ? $workflowUser->section_id : null,
                'Designation' => array_key_exists('Designation', $allInitName[0])
                    ? $workflowUser->designation_id : null,
                'Software Category' => array_key_exists('Software Category', $allInitName[0])
                    ? (int) $validated['software_category_id'] : null,
                'Project' => array_key_exists('Project', $allInitName[0])
                    ? (int) $validated['project_id'] : null,
                'Project MDM' => array_key_exists('Project MDM', $allInitName[0])
                    ? (int) $validated['mdm_project_id'] : null,
                'Form' => array_key_exists('Form', $allInitName[0])
                    ? (int) $validated['form_id'] : null,
            ];
        }
        // dd($combinations);
        // Remove null values from each combination
        $combinations = array_map(function ($combination) {
            // ksort($combination, SORT_NATURAL);
            return array_filter(($combination), function ($value) {
                return $value !== null;
            });
        }, $combinations);

// dd($allInitName, $workflowIds, $combinations);
        // Function to compare arrays
        function compareArrays($array1, $array2): bool
        {
            foreach ($array1 as $key => $value) {
                if (!isset($array2[$key]) || $array2[$key] !== $value) {
                    return false;
                }
            }
            return true;
        }

        // Function to find matching combinations
        function findMatchingCombinations($combinations, $allInitName)
        {
            $matchingSets = [];
            $matchedInitKeys = [];

            if (is_array($combinations[0])) {
                // Multiple arrays in $combinations
                foreach ($combinations as $combination) {
                    foreach ($allInitName as $key => $initName) {
                        if (compareArrays($initName, $combination)) {
                            $matchedInitKeys[] = $key;
                            $matchingSets[] = $combination;
                            break; // Move to the next combination
                        }
                    }
                }
            } else {
                // Single array in $combinations
                foreach ($allInitName as $key => $initName) {
                    if (compareArrays($initName, $combinations)) {
                        $matchedInitKeys[] = $key;
                        $matchingSets[] = $combinations;
                        break; // Move to the next combination
                    }
                }
            }

            return [$matchingSets, $matchedInitKeys];
        }

        list($matchingSets, $matchedInitKeys) = findMatchingCombinations($combinations, $allInitName);

        if (!empty($matchingSets)) {

            $workflowId = $workflowIds[$matchedInitKeys[0]]['workflow_id'];

            $defined = $workflowDefined->where('workflow_id', $workflowId)->first();
            if ($defined && $defined->workflow && $defined->workflow->workflowSubscribersApprovers) {
                return [
                    'workflowId' => $workflowId,
                    'defined' => $defined->workflow->workflowSubscribersApprovers->sortBy('sequence_no')->toArray()
                ];
            }
        }

        return Helper::sendError('Workflow is not defined for these initiator fields.', [], 422);
    }
}






/*
<?php

namespace App\Services;

use App\Http\Helpers\Helper;
use App\Models\Form;
use App\Models\WorkflowInitiatorField;
use Auth;

class GlobalFormStoreService
{
    public function workflowCheck($validated, $modelId, $workflowUser = null)
    {
        $workflowUser = $workflowUser ?? Auth::user();
        $defined = Form::where('id', $modelId)
            ->where(function ($query) {
                $query->whereNotNull('initiator_field_one_id')
                    ->orWhereNotNull('initiator_field_two_id')
                    ->orWhereNotNull('initiator_field_three_id')
                    ->orWhereNotNull('initiator_field_four_id')
                    ->orWhereNotNull('initiator_field_five_id');
            })
            ->exists();
        if (!$defined) {
            return Helper::sendError("Please first assign Initiator Fields then Workflow for this form.", [], 422);
        }
        $form = Form::with(
            'initiator_field_one',
            'initiator_field_two',
            'initiator_field_three',
            'initiator_field_four',
            'initiator_field_five'
        )
            ->find($modelId);
        $workflowDefined = WorkflowInitiatorField::with('workflow.workflowSubscribersApprovers')
            ->where('initiator_id', $workflowUser->id)
            ->where('form_id', $modelId)->get();
// dd($workflowDefined);
        $allInitName = [];
        foreach ($workflowDefined as $values) {
            $workflowId = ['workflow_id' => $values->workflow_id];
            $initName = [];

            if ($values->key_one !== null) {
                $initName[$form->initiator_field_one->name] = $values->key_one;
            }
            if ($values->key_two !== null) {
                $initName[$form->initiator_field_two->name] = $values->key_two;
            }
            if ($values->key_three !== null) {
                $initName[$form->initiator_field_three->name] = $values->key_three;
            }
            if ($values->key_four !== null) {
                $initName[$form->initiator_field_four->name] = $values->key_four;
            }
            if ($values->key_five !== null) {
                $initName[$form->initiator_field_five->name] = $values->key_five;
            }

            $allInitName[] = $initName;
            $workflowIds[] = $workflowId;
        }

        if (empty($allInitName)) {
            return Helper::sendError("Workflow is not defined for you.", [], 422);
        }

        $softwareSubcategories = $validated['software_subcategory_id'] ?? null;

        $combinations = [];

        if (
            array_key_exists('Software Subcategory', $allInitName[0])
            && $softwareSubcategories
            && array_key_exists('Software Category', $allInitName[0])
        ) {
            foreach ($softwareSubcategories as $softwareSubcategory) {
                $combinations[] = [
                    'Location' => array_key_exists('Location', $allInitName[0])
                        ? (int) ($validated['location_id'] ? $validated['location_id'] : $workflowUser->location_id) : null,
                    'Department' => array_key_exists('Department', $allInitName[0])
                        ? (int) ($validated['department_id'] ? $validated['department_id'] : $workflowUser->department_id) : null,
                    'Section' => array_key_exists('Section', $allInitName[0])
                        ? (int) ($validated['section_id'] ? $validated['section_id'] : $workflowUser->section_id) : null,
                    'Designation' => array_key_exists('Designation', $allInitName[0])
                        ? (int) ($validated['designation_id'] ? $validated['designation_id'] : $workflowUser->designation_id) : null,
                    'Software Category' => array_key_exists('Software Category', $allInitName[0])
                        ? (int) $validated['software_category_id'] : null,
                    'Project' => array_key_exists('Project', $allInitName[0])
                        ? (int) $validated['project_id'] : null,
                    'Project MDM' => array_key_exists('Project MDM', $allInitName[0])
                        ? (int) $validated['mdm_project_id'] : null,
                    'Form' => array_key_exists('Form', $allInitName[0])
                        ? (int) $validated['form_id'] : null,
                    'Software Subcategory' => (int) $softwareSubcategory,
                ];
            }
        } else {
            $combinations[] = [
                'Location' => array_key_exists('Location', $allInitName[0])
                    ? (int) $workflowUser->location_id : null,
                'Department' => array_key_exists('Department', $allInitName[0])
                    ? $workflowUser->department_id : null,
                'Section' => array_key_exists('Section', $allInitName[0])
                    ? $workflowUser->section_id : null,
                'Designation' => array_key_exists('Designation', $allInitName[0])
                    ? $workflowUser->designation_id : null,
                'Software Category' => array_key_exists('Software Category', $allInitName[0])
                    ? (int) $validated['software_category_id'] : null,
                'Project' => array_key_exists('Project', $allInitName[0])
                    ? (int) $validated['project_id'] : null,
                'Project MDM' => array_key_exists('Project MDM', $allInitName[0])
                    ? (int) $validated['mdm_project_id'] : null,
                'Form' => array_key_exists('Form', $allInitName[0])
                    ? (int) $validated['form_id'] : null,
            ];
        }
// dd($combinations);
        // Remove null values from each combination
        $combinations = array_map(function ($combination) {
            // ksort($combination, SORT_NATURAL);
            return array_filter(($combination), function ($value) {
                return $value !== null;
            });
        }, $combinations);

        // Function to compare arrays
        function compareArrays($array1, $array2): bool
        {
            foreach ($array1 as $key => $value) {
                if (!isset($array2[$key]) || $array2[$key] !== $value) {
                    return false;
                }
            }
            return true;
        }

        // Function to find matching combinations
        function findMatchingCombinations($combinations, $allInitName)
        {
            $matchingSets = [];
            $matchedInitKeys = [];

            if (is_array($combinations[0])) {
                // Multiple arrays in $combinations
                foreach ($combinations as $combination) {
                    foreach ($allInitName as $key => $initName) {
                        if (compareArrays($initName, $combination)) {
                            $matchedInitKeys[] = $key;
                            $matchingSets[] = $combination;
                            break; // Move to the next combination
                        }
                    }
                }
            } else {
                // Single array in $combinations
                foreach ($allInitName as $key => $initName) {
                    if (compareArrays($initName, $combinations)) {
                        $matchedInitKeys[] = $key;
                        $matchingSets[] = $combinations;
                        break; // Move to the next combination
                    }
                }
            }

            return [$matchingSets, $matchedInitKeys];
        }

        list($matchingSets, $matchedInitKeys) = findMatchingCombinations($combinations, $allInitName);

        if (!empty($matchingSets)) {

            $workflowId = $workflowIds[$matchedInitKeys[0]]['workflow_id'];

            $defined = $workflowDefined->where('workflow_id', $workflowId)->first();
            if ($defined && $defined->workflow && $defined->workflow->workflowSubscribersApprovers) {
                return [
                    'workflowId' => $workflowId,
                    'defined' => $defined->workflow->workflowSubscribersApprovers->sortBy('sequence_no')->toArray()
                ];
            }
        }

        return Helper::sendError('Workflow is not defined for these initiator fields.', [], 422);
    }
}

*/
