<?php

namespace Database\Seeders;

use App\Models\Designation;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AutoAssignTaskPermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'AutoAssignTask-view']);
        Permission::firstOrCreate(['name' => 'AutoAssignTask-create']);
        Permission::firstOrCreate(['name' => 'AutoAssignTask-edit']);
        Permission::firstOrCreate(['name' => 'AutoAssignTask-delete']);

        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            throw new \Exception('Admin role not found. Please create it first.');
        }
        $adminRole->syncPermissions(Permission::all());
    }
}
