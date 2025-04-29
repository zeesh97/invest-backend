<?php

// @formatter:off
/**
 * A helper file for your Eloquent Models
 * Copy the phpDocs from this file to the correct Model,
 * And remove them from this file, to prevent double declarations.
 *
 * @author Barry vd. Heuvel <barryvdh@gmail.com>
 */


namespace App\Models{
/**
 * App\Models\ApprovalStatus
 *
 * @property int|null $workflow_id
 * @property int|null $approver_id
 * @property int|null $user_id
 * @property int $approval_required
 * @property int|null $sequence_no
 * @property int|null $key
 * @property string|null $reason
 * @property string|null $status
 * @property string|null $status_at
 * @property-read \App\Models\Approver|null $approver
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Workflow|null $workflow
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus query()
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus whereApprovalRequired($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus whereApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus whereKey($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus whereReason($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus whereSequenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus whereStatusAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus whereUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|ApprovalStatus whereWorkflowId($value)
 */
	class ApprovalStatus extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Approver
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApprovalStatus> $approvalStatuses
 * @property-read int|null $approval_statuses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Workflow> $workflows
 * @property-read int|null $workflows_count
 * @method static \Illuminate\Database\Eloquent\Builder|Approver newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Approver newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Approver onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Approver query()
 * @method static \Illuminate\Database\Eloquent\Builder|Approver whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Approver whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Approver whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Approver whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Approver whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Approver withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Approver withoutTrashed()
 */
	class Approver extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Attachment
 *
 * @property int $id
 * @property string $attachable_type
 * @property int $attachable_id
 * @property string $filename
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $attachable
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereAttachableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereAttachableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereFilename($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Attachment whereUpdatedAt($value)
 */
	class Attachment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\BusinessExpert
 *
 * @property int $id
 * @property int $software_subcategory_id
 * @property int|null $business_expert_user_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SoftwareSubcategory|null $software_subcategory
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessExpert newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessExpert newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessExpert query()
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessExpert whereBusinessExpertUserId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessExpert whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessExpert whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessExpert whereSoftwareSubcategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|BusinessExpert whereUpdatedAt($value)
 */
	class BusinessExpert extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Comment
 *
 * @property int $id
 * @property int $user_id
 * @property int $scrf_id
 * @property int|null $parent_id
 * @property string $comment
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachables
 * @property-read int|null $attachables_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, Comment> $replies
 * @property-read int|null $replies_count
 * @property-read \App\Models\Forms\SCRF|null $scrf
 * @property-read \App\Models\User $user
 * @method static \Database\Factories\CommentFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment query()
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereComment($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereParentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereScrfId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Comment whereUserId($value)
 */
	class Comment extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Department
 *
 * @property int $id
 * @property string $name
 * @property int|null $location_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FormPermission> $form_permissions
 * @property-read int|null $form_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Forms\SCRF> $scrfs
 * @property-read int|null $scrfs_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Section> $sections
 * @property-read int|null $sections_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Department newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Department onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Department query()
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Department withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Department withoutTrashed()
 */
	class Department extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Designation
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Designation newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Designation newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Designation onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Designation query()
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Designation withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Designation withoutTrashed()
 */
	class Designation extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Form
 *
 * @property int $id
 * @property string $name
 * @property string $identity
 * @property string|null $initiator_field_one_id
 * @property string|null $initiator_field_two_id
 * @property string|null $initiator_field_three_id
 * @property string|null $initiator_field_four_id
 * @property string|null $initiator_field_five_id
 * @property string|null $callback
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\SetupField|null $initiator_field_five
 * @property-read \App\Models\SetupField|null $initiator_field_four
 * @property-read \App\Models\SetupField|null $initiator_field_one
 * @property-read \App\Models\SetupField|null $initiator_field_three
 * @property-read \App\Models\SetupField|null $initiator_field_two
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WorkflowInitiatorField> $workflow_initiator_fields
 * @property-read int|null $workflow_initiator_fields_count
 * @method static \Illuminate\Database\Eloquent\Builder|Form newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Form query()
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereCallback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereIdentity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereInitiatorFieldFiveId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereInitiatorFieldFourId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereInitiatorFieldOneId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereInitiatorFieldThreeId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereInitiatorFieldTwoId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Form whereUpdatedAt($value)
 */
	class Form extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\FormPermission
 *
 * @property int $id
 * @property int $form_id
 * @property int $user_id
 * @property int $setupable_id
 * @property string $setupable_type
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Model|\Eloquent $setupable
 * @method static \Illuminate\Database\Eloquent\Builder|FormPermission newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormPermission newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|FormPermission query()
 * @method static \Illuminate\Database\Eloquent\Builder|FormPermission whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormPermission whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormPermission whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormPermission whereSetupableId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormPermission whereSetupableType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormPermission whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|FormPermission whereUserId($value)
 */
	class FormPermission extends \Eloquent {}
}

namespace App\Models\Forms{
/**
 * App\Models\Forms\QualityAssurance
 *
 * @property int $id
 * @property int|null $scrf_id
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $assignedToUsers
 * @property-read int|null $assigned_to_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $assignedUsers
 * @property-read int|null $assigned_users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $createdByUsers
 * @property-read int|null $created_by_users_count
 * @property-read \App\Models\Forms\SCRF|null $scrf
 * @property-read \App\Models\User|null $user
 * @method static \Illuminate\Database\Eloquent\Builder|QualityAssurance newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityAssurance newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityAssurance query()
 * @method static \Illuminate\Database\Eloquent\Builder|QualityAssurance whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityAssurance whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityAssurance whereScrfId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|QualityAssurance whereUpdatedAt($value)
 */
	class QualityAssurance extends \Eloquent {}
}

namespace App\Models\Forms{
/**
 * App\Models\Forms\SCRF
 *
 * @property int $id
 * @property string|null $sequence_no
 * @property string|null $request_title
 * @property string|null $request_specs
 * @property string|null $change_type
 * @property string|null $change_priority
 * @property float|null $man_hours
 * @property string|null $process_efficiency
 * @property string|null $controls_improved
 * @property string|null $cost_saved
 * @property string|null $legal_reasons
 * @property string $change_significance
 * @property string|null $other_benefits
 * @property int|null $workflow_id
 * @property int|null $software_category_id
 * @property int|null $created_by
 * @property string|null $draft_at
 * @property string|null $status
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachables
 * @property-read int|null $attachables_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Attachment> $attachments
 * @property-read int|null $attachments_count
 * @property-read \App\Models\BusinessExpert|null $business_expert
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\Designation|null $designation
 * @property-read \App\Models\Location $location
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Forms\QualityAssurance> $qualityAssurances
 * @property-read int|null $quality_assurances_count
 * @property-read \App\Models\Section|null $section
 * @property-read \App\Models\SoftwareCategory|null $software_category
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SoftwareSubcategory> $software_subcategories
 * @property-read int|null $software_subcategories_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Forms\UatScenario> $uatScenarios
 * @property-read int|null $uat_scenarios_count
 * @property-read \App\Models\User|null $user
 * @property-read \App\Models\Workflow|null $workflow
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF query()
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereChangePriority($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereChangeSignificance($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereChangeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereControlsImproved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereCostSaved($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereCreatedBy($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereDraftAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereLegalReasons($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereManHours($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereOtherBenefits($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereProcessEfficiency($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereRequestSpecs($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereRequestTitle($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereSequenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereSoftwareCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF whereWorkflowId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|SCRF withoutTrashed()
 */
	class SCRF extends \Eloquent {}
}

namespace App\Models\Forms{
/**
 * App\Models\Forms\UatScenario
 *
 * @property int $id
 * @property int|null $scrf_id
 * @property string $detail
 * @property string $status
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Forms\SCRF|null $scrf
 * @method static \Illuminate\Database\Eloquent\Builder|UatScenario newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UatScenario newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|UatScenario query()
 * @method static \Illuminate\Database\Eloquent\Builder|UatScenario whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UatScenario whereDetail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UatScenario whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UatScenario whereScrfId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UatScenario whereStatus($value)
 * @method static \Illuminate\Database\Eloquent\Builder|UatScenario whereUpdatedAt($value)
 */
	class UatScenario extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Location
 *
 * @property int $id
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\FormPermission> $form_permissions
 * @property-read int|null $form_permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Location newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Location onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Location query()
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Location withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Location withoutTrashed()
 */
	class Location extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Section
 *
 * @property int $id
 * @property int|null $department_id
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \App\Models\Department|null $department
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @method static \Illuminate\Database\Eloquent\Builder|Section newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Section newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Section onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Section query()
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Section withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Section withoutTrashed()
 */
	class Section extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SetupField
 *
 * @property int $id
 * @property string $name
 * @property string $identity
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WorkflowInitiatorField> $formKeys
 * @property-read int|null $form_keys_count
 * @method static \Database\Factories\SetupFieldFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|SetupField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SetupField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SetupField query()
 * @method static \Illuminate\Database\Eloquent\Builder|SetupField whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SetupField whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SetupField whereIdentity($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SetupField whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SetupField whereUpdatedAt($value)
 */
	class SetupField extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SoftwareCategory
 *
 * @property int $id
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\SoftwareSubcategory> $software_subcategories
 * @property-read int|null $software_subcategories_count
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareCategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareCategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareCategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareCategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareCategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareCategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareCategory whereUpdatedAt($value)
 */
	class SoftwareCategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\SoftwareSubcategory
 *
 * @property int $id
 * @property int $software_category_id
 * @property string|null $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Forms\SCRF> $scrfs
 * @property-read int|null $scrfs_count
 * @property-read \App\Models\SoftwareCategory $software_category
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSubcategory newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSubcategory newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSubcategory query()
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSubcategory whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSubcategory whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSubcategory whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSubcategory whereSoftwareCategoryId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|SoftwareSubcategory whereUpdatedAt($value)
 */
	class SoftwareSubcategory extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Subscriber
 *
 * @property int $id
 * @property string $name
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\User> $users
 * @property-read int|null $users_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Workflow> $workflows
 * @property-read int|null $workflows_count
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber query()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|Subscriber withoutTrashed()
 */
	class Subscriber extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Tenant
 *
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Tenant query()
 */
	class Tenant extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\User
 *
 * @property int $id
 * @property string $name
 * @property string $email
 * @property \Illuminate\Support\Carbon|null $email_verified_at
 * @property string $password
 * @property string|null $remember_token
 * @property string|null $profile_photo_path
 * @property int|null $department_id
 * @property int|null $designation_id
 * @property int|null $location_id
 * @property int|null $section_id
 * @property string $employee_no
 * @property string $employee_type
 * @property string|null $phone_number
 * @property string|null $extension
 * @property \Illuminate\Support\Carbon|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApprovalStatus> $approvalStatus
 * @property-read int|null $approval_status_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Approver> $approvers
 * @property-read int|null $approvers_count
 * @property-read \App\Models\BusinessExpert $businessExpert
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Comment> $comments
 * @property-read int|null $comments_count
 * @property-read \App\Models\Department|null $department
 * @property-read \App\Models\Designation|null $designation
 * @property-read \App\Models\Location|null $location
 * @property-read \Illuminate\Notifications\DatabaseNotificationCollection<int, \Illuminate\Notifications\DatabaseNotification> $notifications
 * @property-read int|null $notifications_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Permission> $permissions
 * @property-read int|null $permissions_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Spatie\Permission\Models\Role> $roles
 * @property-read int|null $roles_count
 * @property-read \App\Models\Section|null $section
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subscriber> $subscribers
 * @property-read int|null $subscribers_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \Laravel\Sanctum\PersonalAccessToken> $tokens
 * @property-read int|null $tokens_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Workflow> $workflowInitiators
 * @property-read int|null $workflow_initiators_count
 * @method static \Database\Factories\UserFactory factory($count = null, $state = [])
 * @method static \Illuminate\Database\Eloquent\Builder|User newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|User onlyTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User permission($permissions)
 * @method static \Illuminate\Database\Eloquent\Builder|User query()
 * @method static \Illuminate\Database\Eloquent\Builder|User role($roles, $guard = null)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDepartmentId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereDesignationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmail($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmailVerifiedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmployeeNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereEmployeeType($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereExtension($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereLocationId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePassword($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User wherePhoneNumber($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereProfilePhotoPath($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereRememberToken($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereSectionId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|User withTrashed()
 * @method static \Illuminate\Database\Eloquent\Builder|User withoutTrashed()
 */
	class User extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\Workflow
 *
 * @property int $id
 * @property string|null $name
 * @property int|null $created_by_id
 * @property string|null $callback
 * @property string|null $deleted_at
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\ApprovalStatus> $approvalStatuses
 * @property-read int|null $approval_statuses_count
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Approver> $approvers
 * @property-read int|null $approvers_count
 * @property-read \App\Models\User|null $created_by
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\Subscriber> $subscribers
 * @property-read int|null $subscribers_count
 * @property-read \App\Models\WorkflowInitiatorField|null $workflowInitiatorField
 * @property-read \Illuminate\Database\Eloquent\Collection<int, \App\Models\WorkflowSubscriberApprover> $workflowSubscribersApprovers
 * @property-read int|null $workflow_subscribers_approvers_count
 * @method static \Illuminate\Database\Eloquent\Builder|Workflow newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Workflow newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|Workflow query()
 * @method static \Illuminate\Database\Eloquent\Builder|Workflow whereCallback($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Workflow whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Workflow whereCreatedById($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Workflow whereDeletedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Workflow whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Workflow whereName($value)
 * @method static \Illuminate\Database\Eloquent\Builder|Workflow whereUpdatedAt($value)
 */
	class Workflow extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\WorkflowInitiatorField
 *
 * @property int|null $workflow_id
 * @property int|null $form_id
 * @property int|null $initiator_id
 * @property int $key_one
 * @property int $key_two
 * @property int $key_three
 * @property int $key_four
 * @property int $key_five
 * @property-read \App\Models\Form|null $form
 * @property-read \App\Models\Workflow|null $workflow
 * @property-read \App\Models\User|null $workflowInitiator
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowInitiatorField newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowInitiatorField newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowInitiatorField query()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowInitiatorField whereFormId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowInitiatorField whereInitiatorId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowInitiatorField whereKeyFive($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowInitiatorField whereKeyFour($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowInitiatorField whereKeyOne($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowInitiatorField whereKeyThree($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowInitiatorField whereKeyTwo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowInitiatorField whereWorkflowId($value)
 */
	class WorkflowInitiatorField extends \Eloquent {}
}

namespace App\Models{
/**
 * App\Models\WorkflowSubscriberApprover
 *
 * @property int $id
 * @property int|null $workflow_id
 * @property int|null $approver_id
 * @property int|null $subscriber_id
 * @property string|null $approval_condition
 * @property int|null $sequence_no
 * @property \Illuminate\Support\Carbon|null $created_at
 * @property \Illuminate\Support\Carbon|null $updated_at
 * @property-read \App\Models\Approver|null $approver
 * @property-read \App\Models\Subscriber|null $subscriber
 * @property-read \App\Models\Workflow|null $workflow
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowSubscriberApprover newModelQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowSubscriberApprover newQuery()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowSubscriberApprover query()
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowSubscriberApprover whereApprovalCondition($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowSubscriberApprover whereApproverId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowSubscriberApprover whereCreatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowSubscriberApprover whereId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowSubscriberApprover whereSequenceNo($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowSubscriberApprover whereSubscriberId($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowSubscriberApprover whereUpdatedAt($value)
 * @method static \Illuminate\Database\Eloquent\Builder|WorkflowSubscriberApprover whereWorkflowId($value)
 */
	class WorkflowSubscriberApprover extends \Eloquent {}
}

