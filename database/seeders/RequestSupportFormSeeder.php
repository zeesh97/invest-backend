<?php

namespace Database\Seeders;

use App\Models\Designation;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RequestSupportFormSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Permission::firstOrCreate(['name' => 'RequestSupportForm-view']);
        Permission::firstOrCreate(['name' => 'RequestSupportForm-create']);
        Permission::firstOrCreate(['name' => 'RequestSupportForm-edit']);
        Permission::firstOrCreate(['name' => 'RequestSupportForm-delete']);

        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            throw new \Exception('Admin role not found. Please create it first.');
        }
        $adminRole->syncPermissions(Permission::all());
    }
}
