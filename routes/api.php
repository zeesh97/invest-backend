<?php

use App\Http\Controllers\ActivityLogController;
use App\Http\Controllers\ApproverController;
use App\Http\Controllers\AssignTaskController;
use App\Http\Controllers\AttachmentController;
use App\Http\Controllers\AuthController;
use App\Http\Controllers\AutoAssignTaskController;
use App\Http\Controllers\BackupController;
use App\Http\Controllers\BusinessExpertController;
use App\Http\Controllers\CallbackController;
use App\Http\Controllers\ConditionController;
use App\Http\Controllers\CoreProcessController;
use App\Http\Controllers\CostCenterController;
use App\Http\Controllers\DepartmentController;
use App\Http\Controllers\DesignationController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\EmailLogController;
use App\Http\Controllers\EquipmentController;
use App\Http\Controllers\EquipmentRequirementController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormPermissionController;
use App\Http\Controllers\FormRoleUserController;
use App\Http\Controllers\Forms\CommentController;
use App\Http\Controllers\Forms\QualityAssuranceController;
use App\Http\Controllers\Forms\SCRFController;
use App\Http\Controllers\Forms\CRFController;
use App\Http\Controllers\Forms\DeploymentController;
use App\Http\Controllers\Forms\MasterDataManagementFormController;
use App\Http\Controllers\Forms\MobileRequisitionController;
use App\Http\Controllers\LocationController;
use App\Http\Controllers\MdmCategoryController;
use App\Http\Controllers\NonFormController;
use App\Http\Controllers\ParallelApproverController;
use App\Http\Controllers\PermissionController;
use App\Http\Controllers\QaAssignmentController;
use App\Http\Controllers\RoleController;
use App\Http\Controllers\SectionController;
use App\Http\Controllers\ServiceDeskController;
use App\Http\Controllers\SettingController;
use App\Http\Controllers\SetupFieldController;
use App\Http\Controllers\SoftwareCategoryController;
use App\Http\Controllers\SoftwareSubcategoryController;
use App\Http\Controllers\SubscriberController;
use App\Http\Controllers\RequestSupportFormController;
use App\Http\Controllers\TaskStatusNameController;
use App\Http\Controllers\TeamController;
use App\Http\Controllers\TeamMemberController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\WorkflowController;
use App\Services\DashboardService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Resources\UserResource;
use App\Mail\LaravelTenTestMail;
use App\Models\Form;
use App\Models\Forms\Deployment;
use App\Models\ServiceDesk;
use App\Services\SendQaRequestService;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

use App\Http\Controllers\MakeController;

use App\Http\Controllers\CompanyController;
use App\Http\Controllers\DependentCrudController;
use App\Http\Controllers\Forms\SapAccessFormController;
use App\Http\Controllers\ImpersonationController;
use App\Http\Controllers\IpRestrictionController;
use App\Http\Controllers\OtherDependentController;
use App\Http\Controllers\ProjectController;
use App\Http\Controllers\ProjectMDMController;
// use App\Http\Controllers\PackageController;
use App\Http\Controllers\RequestSupportDeskController;
use App\Http\Controllers\ServiceController;
use App\Http\Controllers\SubscriptionController;
use App\Http\Controllers\TimezoneController;
use App\Http\Controllers\WithoutWorkflowController;
use App\Mail\TestEmail;
use App\Models\Service;
use App\Services\DepartmentService;
use App\Services\ServiceTeam;
use App\Services\UploadBulkUsersService;
use App\Services\UserService;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::middleware(['auth:sanctum'])->get('/user', function (Request $request) {
    return new UserResource($request->user());
});

// Route::group(['middleware' => ['tenant']], function() {
Route::post('login', [AuthController::class, 'login']);
Route::post('super-admin-login3032825', [AuthController::class, 'superAdminLogin']);
// });

Route::group(['middleware' => ['auth:sanctum', 'verified', 'cors', 'auth.session.timeout', 'impersonate']], function () {
    Route::post('register', [UserController::class, 'store']);
    Route::post('store-software-category', [BusinessExpertController::class, 'storeSoftwareCategory']);
    Route::post('get-software-subcategories/', [BusinessExpertController::class, 'getSoftwareSubcategory']);
    Route::post('status-quality-assurance', [QualityAssuranceController::class, 'statusQualityAssurance']);
    Route::get('list-quality-assurance', [QualityAssuranceController::class, 'index']);
    Route::get('current-subscriptions', [SubscriptionController::class, 'showCurrentSubscriptions']);
    Route::apiResource('qa-assign', QaAssignmentController::class)->only(['index', 'store', 'update']);
    Route::get('get-qa-assigned/add', [QaAssignmentController::class, 'ifQaAssigned']);
    Route::get('backups', [BackupController::class, 'getBackups']);
    Route::get('activity-logs', [ActivityLogController::class, 'getActivitiesByModel']);
    // Route::get('activity-logs', [ActivityLogController::class, 'getActivitiesByRecord']);
    Route::apiResource('other-dependents', OtherDependentController::class);

    Route::get('backups/download/{file}', [BackupController::class, 'downloadBackup']);
    Route::put('/deployment-status-update/{deployment}', [DeploymentController::class, 'deploymentStatusChange']);
    Route::get('get-current-workflow', [CoreProcessController::class, 'getCurrentWorkflow']);
    Route::post('update-initiated-workflow', [WorkflowController::class, 'updateInitiatedWorkflow']);
    Route::delete('/delete-workflow-approver-group', [WorkflowController::class, 'deleteWorkflowGroup']);

    Route::get('/attachment/{id}', [AttachmentController::class, 'download'])->name('attachment.download');
    Route::apiResource('attachments', AttachmentController::class)->only(['store', 'destroy']);

    Route::get('approvers/all', [ApproverController::class, 'all']);
    Route::get('get-workflow-types', [WorkflowController::class, 'workflow_type']);
    Route::get('get-sections-of-department', [SectionController::class, 'departmentBySectionId']);
    Route::get('get-projects-of-form', [ProjectController::class, 'projectByFormId']);

    /************************* Forms start *********************** */
    Route::apiResource('quality-assurance', QualityAssuranceController::class);
    Route::apiResource('scrf', SCRFController::class);
    Route::apiResource('deployment-form', DeploymentController::class);
    Route::apiResource('crf', CRFController::class);
    Route::apiResource('mobile-requisition', MobileRequisitionController::class);
    Route::apiResource('master-data-management', MasterDataManagementFormController::class);
    Route::apiResource('sap-access-form', SapAccessFormController::class);
    /************************* Forms end *********************** */


    Route::apiResource('subscriptions', SubscriptionController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('ip-restrictions', IpRestrictionController::class)->only(['index', 'store', 'destroy']);
    // Route::apiResource('packages', PackageController::class);
    Route::apiResource('conditions', ConditionController::class);
    Route::apiResource('email-logs', EmailLogController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('software-subcategories', SoftwareSubcategoryController::class);
    Route::apiResource('callbacks', CallbackController::class);
    Route::apiResource('software-categories', SoftwareCategoryController::class);
    Route::apiResource('departments', DepartmentController::class);
    Route::apiResource('services', ServiceController::class);
    Route::apiResource('mdm_categories', MdmCategoryController::class);
    Route::apiResource('business-experts', BusinessExpertController::class);
    Route::apiResource('parallel-approver', ParallelApproverController::class);
    Route::get('setup-fields/all', [SetupFieldController::class, 'all']);
    Route::get('forms/all', [FormController::class, 'all']);
    Route::get('non-forms/all', [FormController::class, 'all']);
    Route::post('get-form-details', [FormController::class, 'getFormDetails']);
    Route::post('approve-disapproved', [CoreProcessController::class, 'approveDisapprove']);
    Route::post('parallel-approve-disapproved', [CoreProcessController::class, 'parallelApproveDisapprove']);
    Route::apiResource('service-desk', ServiceDeskController::class)->only(['index']);
    Route::apiResource('request-support-form', RequestSupportFormController::class);
    Route::apiResource('request-support-desk', RequestSupportDeskController::class)->only(['index']);
    Route::apiResource('dependent-cruds', DependentCrudController::class)->only(['index', 'store', 'destroy']);
    Route::apiResource('comments', CommentController::class)->only(['index', 'store']);
    Route::get('comments-non-form', [CommentController::class, 'indexNonForm']);
    Route::post('comments-non-form', [CommentController::class, 'storeNonForm']);
    Route::put('enable-disable-comments', [CommentController::class, 'enableDisableComments']);
    Route::put('enable-disable-comments-non-form', [CommentController::class, 'enableDisableCommentsNonForm']);


    Route::apiResource('auto-assign-tasks', AutoAssignTaskController::class);
    Route::post('/impersonate/{userId}', [ImpersonationController::class, 'impersonate']);
    Route::post('/impersonate-stop', [ImpersonationController::class, 'stopImpersonating']);

    Route::post('/departments/{department}/services/attach', [DepartmentService::class, 'attachServices']);
    Route::post('/departments/{department}/services/detach', [DepartmentService::class, 'detachServices']);
    Route::get('/department-services', [DepartmentService::class, 'index']);
    Route::get('/department-services/{id}', [DepartmentService::class, 'show']);

    Route::post('/services/{service}/teams/attach', [ServiceTeam::class, 'attachTeams']);
    Route::post('/services/{service}/teams/detach', [ServiceTeam::class, 'detachTeams']);
    Route::get('/service-teams', [ServiceTeam::class, 'index']);
    Route::get('/service-teams/{id}', [ServiceTeam::class, 'show']);

    Route::apiResource('setup-fields', SetupFieldController::class)->except(['destroy', 'store']);
    Route::apiResource('form-permissions', FormPermissionController::class);
    Route::apiResource('form-role-users', FormRoleUserController::class);
    Route::apiResource('forms', FormController::class)->except(['destroy', 'store']);
    Route::apiResource('non-forms', NonFormController::class)->except(['destroy', 'store']);
    Route::apiResource('teams', TeamController::class);
    Route::get('team-by-form-id', [TeamController::class, 'teamByForm']);
    Route::get('team-members-by-id', [TeamController::class, 'teamMembersById']);
    Route::put('update-task-status', [AssignTaskController::class, 'updateTaskStatus']);
    Route::put('update-task-status-non-form', [AssignTaskController::class, 'updateTaskStatusNonForm']);
    Route::apiResource('assign-tasks', AssignTaskController::class)->except(['destroy']);
    Route::apiResource('team-members', TeamMemberController::class)->except(['destroy', 'show']);
    Route::apiResource('approvers', ApproverController::class);
    Route::apiResource('task-status-names', TaskStatusNameController::class)->only(['index']);
    Route::apiResource('cost-centers', CostCenterController::class)->except(['show']);
    Route::apiResource('mdm-projects', ProjectMDMController::class);
    Route::apiResource('equipments', EquipmentController::class);
    Route::apiResource('subscribers', SubscriberController::class);
    Route::apiResource('workflows', WorkflowController::class);
    Route::apiResource('without-workflows', WithoutWorkflowController::class);
    Route::apiResource('sections', SectionController::class);
    Route::apiResource('projects', ProjectController::class);
    Route::apiResource('locations', LocationController::class);
    Route::apiResource('designations', DesignationController::class);
    Route::apiResource('permissions', PermissionController::class);
    Route::apiResource('roles', RoleController::class);
    Route::apiResource('users', UserController::class)->except(['store']);
    Route::get('search-title', [MasterDataManagementFormController::class, 'searchTitle']);
    Route::get('search-employee-name', [UserService::class, 'searchEmployeeName']);
    Route::get('settings', [SettingController::class, 'index']);
    Route::put('settings', [SettingController::class, 'update']);
    Route::post('profile-picture/{user_id}', [UserController::class, 'updateProfilePicture']);
    Route::put('update-password/{user_id}', [UserController::class, 'updatePassword']);
    Route::get('get-all-teams', [TeamController::class, 'getAllTeams']);
    Route::get('get-all-equipments', [UserController::class, 'getAllEquipments']);
    Route::get('get-all-approvers', [UserController::class, 'getAllApprovers']);
    Route::get('get-all-locations', [UserController::class, 'getAllLocations']);
    Route::get('get-all-cost-centers', [UserController::class, 'getAllCostCenters']);
    Route::get('get-related-cost-centers', [UserController::class, 'getRelatedCostCenters']);
    Route::get('get-all-mdm-projects', [ProjectMDMController::class, 'getAllProjectMDMs']);
    Route::get('get-related-mdm-projects', [ProjectMDMController::class, 'getRelatedProjectMDMs']);
    Route::get('get-all-sections', [UserController::class, 'getAllSections']);
    Route::get('get-all-departments', [UserController::class, 'getAllDepartments']);
    Route::get('get-all-designations', [UserController::class, 'getAllDesignations']);
    Route::get('get-all-business-experts', [UserController::class, 'getAllBusinessExperts']);
    Route::get('get-all-software-categories', [UserController::class, 'getAllSoftwareCategories']);
    Route::get('get-all-software-subcategories', [UserController::class, 'getAllSoftwareSubcategories']);
    Route::get('software-category/{categoryId}/subcategories', [UserController::class, 'getSoftwareSubcategoriesByCategory']);
    Route::get('/timezones', [TimezoneController::class, 'getTimezones']); // or your preferred route
    /* Start Search Routes */
    Route::get('/users-search', [UserController::class, 'search']);
    Route::get('/scrf-filters', [SCRFController::class, 'filters'])->name('scrf.filters');
    Route::get('/sap-access-form-filters', [SapAccessFormController::class, 'filters'])->name('sap-access-form.filters');
    Route::get('/deployment-form-filters', [DeploymentController::class, 'filters'])->name('deployment.filters');
    Route::get('/master-data-management-filters', [MasterDataManagementFormController::class, 'filters'])->name('master-data-management.filters');
    Route::get('/crf-filters', [CRFController::class, 'filters'])->name('crf.filters');
    Route::get('/search-records-by-forms', [DeploymentController::class, 'searchTitleByFormId'])->name('searchRecordsByFormId');

    // Route::get('/service-desk-filters', [ServiceDeskController::class, 'filters'])->name('service-desk.filters');
    /* End  Search Routes */
    Route::get('get-pending-for-approval-counts', [DashboardService::class, 'getPendingForApprovalCount']);
    Route::get('get-approved-or-disapproved-counts', [DashboardService::class, 'getAppOrRejForApprovalCount']);
    Route::get('get-approval-statuses-counts/{form_id}', [DashboardService::class, 'getapprovalStatuses']);
    Route::get('get-approvals-by-form-id', [DashboardService::class, 'getApprovalsByFormId']);
    Route::get('send-qa-request', [SendQaRequestService::class, 'sendQaRequest']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Route::post('upload-bulk-users', [UploadBulkUsersService::class, 'importUsers']);
    Route::apiResource('makes', MakeController::class);
    Route::get('get-all-makes', [UserController::class, 'getAllDepartments']);

    Route::apiResource('companies', CompanyController::class);
    Route::get('/companies-search', [CompanyController::class, 'search']);
});
    // Route::get('/test', function () {
    //     return Storage::disk('google')->put('testttt.txt', 'Hello World');
    // });
// Route::post(
//     '/test-assign',
//     function () {
//         Form::create(['name' => 'Mobile Requisition', 'identity' => 'App\\Models\\Forms\\MobileRequisition']);
//         $permissions['MobileRequisition-view'] = Permission::create(['name' => 'MobileRequisition-view']);

//         $admin_role = Role::find(1);
//         $admin_role->givePermissionTo(Permission::all());
//         if ($admin_role) {
//             dd('ok');
//         }
//     }
// );
// Route::get('/test-mail',function(){

//     $message = "Testing mail";

    // \Mail::raw('Hi, welcome ....!', function ($message) {
    //   $message->to('fortemppp@gmail.com')
    //     ->subject('Testing mail');
    // });
    // Mail::raw('Hi, welcome...', function($message) {
    //     $message->to('fortemppp@gmail.com')
    //             ->subject('Welcome')
    //             ->html('Hi, welcome...');
    // });

//     $data = ['message' => 'Hello from Laravel 10!'];
// \Mail::to('workflow@signaps.com')->queue(new LaravelTenTestMail($data));

//     dd('sent');
// });
// Route::get('/image/{name}', [BackupController::class, 'downloadBackup']);
//     Route::get('/add-code-3032825', function () {
//         $role = Role::where('name', 'admin')->first();

// // Retrieve all permissions
// $permissions = Permission::all();

// // Sync the admin role with all permissions
// $role->syncPermissions($permissions);
//         $permissions = [
//             'AssignTask-view',
//             'SoftwareChangeRequestForm-view',
//             'CapitalRequestForm-view',
//             'QualityAssurance-view',
//             'Deployment-view',
//         ];

//         foreach ($permissions as $permissionName) {
//             Spatie\Permission\Models\Permission::firstOrCreate(['name' => $permissionName]);
//         }

//         return 'Added Successfully';
//     });
