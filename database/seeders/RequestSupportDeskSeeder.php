<?php

namespace Database\Seeders;

use App\Models\Designation;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RequestSupportDeskSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        Permission::firstOrCreate(['name' => 'RequestSupportDesk-view']);

        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            throw new \Exception('Admin role not found. Please create it first.');
        }
        $adminRole->syncPermissions(Permission::all());
    }
}
