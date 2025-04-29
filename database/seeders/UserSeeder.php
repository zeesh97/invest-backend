<?php

namespace Database\Seeders;

use App\Models\Condition;
use App\Models\CostCenter;
use App\Models\Equipment;
use App\Models\Form;
use App\Models\Section;
use App\Models\Setting;
use App\Models\SoftwareCategory;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Location;
use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Carbon;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        if (!Form::find(1)) {
            Location::insert([
                ['name' => 'Karachi'],
                ['name' => 'Lahore'],
                ['name' => 'Islamabad'],
                ['name' => 'Rawalpindi'],
                ['name' => 'Faisalabad'],
                ['name' => 'Hyderabad'],
            ]);

            for ($i = 0; $i < 10; $i++) {
                Designation::create([
                    'name' => 'Designation ' . $i,
                ]);
            }

            for ($i = 0; $i < 20; $i++) {
                Department::create([
                    'name' => 'Department ' . $i,
                ]);
            }

            Designation::create([
                'name' => 'HR Manager',
            ]);

            Setting::create([
                'max_upload_size' => '2',
                'allowed_extensions' => 'jpg,jpeg,gif,png,bmp,xls,xlsx,pdf,doc,docx,ppt,pptx,txt,csv',
            ]);

            $departmentIds = DB::table('departments')->pluck('id')->toArray();

            if (!empty($departmentIds)) {
                for ($i = 0; $i < 10; $i++) {
                    DB::table('sections')->insert([
                        'department_id' => $departmentIds[array_rand($departmentIds)],
                        'name' => 'Section ' . $i,
                        'created_at' => now(),
                    ]);
                }
            } else {
                echo 'No departments found.';
            }

            Department::create(['name' => 'HR']);
            Section::create([
                'department_id' => 1,
                'name' => "Recruitment"
            ]);

            $permissions = [];
            $permissionNames = [
                'Role-list',
                'Role-view',
                'Role-edit',
                'Role-create',
                'Role-delete',
                'Permission-list',
                'Permission-view',
                'Permission-edit',
                'Permission-create',
                'Permission-delete',
                'Department-list',
                'Department-view',
                'Department-edit',
                'Department-create',
                'Department-delete',
                'Section-list',
                'Section-view',
                'Section-edit',
                'Section-create',
                'Section-delete',
                'BusinessExpert-list',
                'BusinessExpert-view',
                'BusinessExpert-edit',
                'BusinessExpert-create',
                'BusinessExpert-delete',
                'Location-list',
                'Location-view',
                'Location-edit',
                'Location-create',
                'Location-delete',
                'Designation-list',
                'Designation-view',
                'Designation-edit',
                'Designation-create',
                'Designation-delete',
                'Approvers-list',
                'Approvers-view',
                'Approvers-edit',
                'Approvers-create',
                'Approvers-delete',
                'Subscribers-list',
                'Subscribers-view',
                'Subscribers-edit',
                'Subscribers-create',
                'Subscribers-delete',
                'SoftwareCategory-list',
                'SoftwareCategory-view',
                'SoftwareCategory-edit',
                'SoftwareCategory-create',
                'SoftwareCategory-delete',
                'SoftwareSubcategory-list',
                'SoftwareSubcategory-view',
                'SoftwareSubcategory-edit',
                'SoftwareSubcategory-create',
                'SoftwareSubcategory-delete',
                'CostCenter-list',
                'CostCenter-view',
                'CostCenter-edit',
                'CostCenter-create',
                'CostCenter-delete',
                'ParallelApprover-list',
                'ParallelApprover-view',
                'ParallelApprover-edit',
                'ParallelApprover-create',
                'ParallelApprover-delete',
                'Equipment-list',
                'Equipment-view',
                'Equipment-edit',
                'Equipment-create',
                'Equipment-delete',
                'User-list',
                'User-view',
                'User-edit',
                'User-create',
                'User-delete',
                'ServiceDesk-view',
                'SoftwareChangeRequestForm-view',
                'CapitalRequestForm-view',
                'QualityAssurance-view',
                'Deployment-view',
                'AssignTask-view',
                'AssignTask-create',
                'AssignTask-edit',
                'MobileRequisition-view',
                'QaFeedbackViewAll'
            ];

            foreach ($permissionNames as $permissionName) {
                $permissions[$permissionName] = Permission::create(['name' => $permissionName]);
            }

            $admin_role = Role::create(['name' => 'admin']);
            $admin_role->givePermissionTo(Permission::all());

            $admin = User::create([
                'name' => 'Admin',
                'email' => 'admin@admin.com',
                'password' => bcrypt('password'),
                'department_id' => '1',
                'designation_id' => '1',
                'location_id' => '5',
                'section_id' => '1',
                'employee_no' => 'qm-98279',
                'employee_type' => 'Permamnent',
                'extension' => '2156',
                'phone_number' => '02112234567'
            ]);

            $admin->assignRole($admin_role);
            $admin->givePermissionTo([
                'User-list',
                'User-view',
                'User-edit',
                'User-create',
                'User-delete'
            ]);

            $user_role = Role::create(['name' => 'user']);
            $user = User::create([
                'name' => 'User Name',
                'email' => 'user@user.com',
                'password' => bcrypt('password'),
                'department_id' => '1',
                'designation_id' => '1',
                'location_id' => '5',
                'section_id' => '1',
                'employee_no' => 'qm-' . rand(10000, 99999),
                'employee_type' => 'Permanent',
                'extension' => rand(100, 999),
                'phone_number' => '021' . rand(1000000, 9999999)
            ]);

            $user->assignRole($user_role);
            $user->givePermissionTo([
                'User-list',
                'User-view'
            ]);
            $user_role->givePermissionTo([
                'User-list',
                'User-view'
            ]);

            for ($i = 0; $i < 20; $i++) {
                $user = User::create([
                    'name' => 'User ' . $i,
                    'email' => 'user' . $i . '@example.com',
                    'password' => bcrypt('password'),
                    'designation_id' => rand(1, 8),
                    'department_id' => rand(1, 8),
                    'location_id' => rand(1, 5),
                    'section_id' => rand(1, 3),
                    'employee_no' => 'qm-' . rand(10000, 99999),
                    'employee_type' => 'Permanent',
                    'extension' => rand(100, 999),
                    'phone_number' => '021' . rand(1000000, 9999999)
                ]);
                $user->assignRole($user_role);
            }

            // Seed software_category table
            for ($i = 0; $i < 20; $i++) {
                DB::table('software_categories')->insert([
                    'name' => 'Software Category ' . $i,
                ]);
            }

            // Seed software_subcategory table
            $softwareCategoryIds = DB::table('software_categories')->pluck('id')->toArray();
            for ($i = 0; $i < 20; $i++) {
                DB::table('software_subcategories')->insert([
                    'software_category_id' => $softwareCategoryIds[array_rand($softwareCategoryIds)],
                    'name' => 'Software Subcategory ' . $i
                ]);
            }

            // Seed business_expert table
            $softwareSubcategoryIds = DB::table('software_subcategories')->pluck('id')->toArray();
            $userIds = DB::table('users')->pluck('id')->toArray();
            for ($i = 0; $i < 20; $i++) {
                DB::table('business_experts')->insert([
                    'software_subcategory_id' => $softwareSubcategoryIds[array_rand($softwareSubcategoryIds)],
                    'business_expert_user_id' => $userIds[array_rand($userIds)],
                ]);
            }

            Form::insert([
                [
                    'name' => 'Quality Assurance',
                    'identity' => "App\\Models\\Forms\\QualityAssurance",
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'SCRF',
                    'identity' => "App\\Models\\Forms\\SCRF",
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Deployment',
                    'identity' => "App\\Models\\Forms\\Deployment",
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'CRF',
                    'identity' => "App\\Models\\Forms\\CRF",
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Mobile Requisition Form',
                    'identity' => "App\\Models\\Forms\\MobileRequisition",
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);

            Condition::insert([
                [
                    'name' => 'SCRF is Major',
                    'form_id' => 2,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'SCRF is Minor',
                    'form_id' => 2,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'Location is Karachi',
                    'form_id' => 2,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'SCRF date is between 1st Jan 24 and 31st Dec 24',
                    'form_id' => 2,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'CRF Amount is in CFO Approval Limit',
                    'form_id' => 4,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'CRF Amount is in Finance HOD Limit',
                    'form_id' => 4,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'CRF is Capital',
                    'form_id' => 4,
                    'created_at' => Carbon::now(),
                ],
                [
                    'name' => 'CRF is Revenue',
                    'form_id' => 4,
                    'created_at' => Carbon::now(),
                ],
            ]);

            // CostCenter::insert([
            //     [
            //         'name' => 'Cost Center 1',
            //         'created_at' => Carbon::now(),
            //         'updated_at' => Carbon::now(),
            //     ],
            //     [
            //         'name' => 'Cost Center 2',
            //         'created_at' => Carbon::now(),
            //         'updated_at' => Carbon::now(),
            //     ],
            // ]);

            Equipment::insert([
                [
                    'name' => 'Laptop',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Desktop',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Server',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
                [
                    'name' => 'Scanner',
                    'created_at' => Carbon::now(),
                    'updated_at' => Carbon::now(),
                ],
            ]);
        }
    }
}
