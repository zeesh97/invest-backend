<?php

namespace Database\Seeders;

use App\Models\Designation;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MdmCategorySeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        Permission::firstOrCreate(['name' => 'MdmCategory-view']);
        Permission::firstOrCreate(['name' => 'MdmCategory-create']);
        Permission::firstOrCreate(['name' => 'MdmCategory-edit']);
        Permission::firstOrCreate(['name' => 'MdmCategory-delete']);

        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            throw new \Exception('Admin role not found. Please create it first.');
        }
        $adminRole->syncPermissions(Permission::all());

        // \Log::info("'MDM Category' Permissions created and assigned to the 'admin' role.");
    }
}



