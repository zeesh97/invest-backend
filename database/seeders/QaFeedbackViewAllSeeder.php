<?php

namespace Database\Seeders;

use App\Models\Designation;
use DB;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class QaFeedbackViewAllSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {

        // $designations = 'Designation';
        // $data = [];

        // for ($i = 10; $i < 100; $i++) {
        //     $data[] = [
        //         'name' => $designations . ' ' . $i,
        //         // Add other columns if needed, e.g., 'created_at' => now(),
        //     ];
        // }

        // Designation::insert($data);
        $permission = Permission::firstOrCreate(['name' => 'QaFeedbackViewAll']);

        $adminRole = Role::where('name', 'admin')->first();
        if (!$adminRole) {
            throw new \Exception('Admin role not found. Please create it first.');
        }
        $adminRole->syncPermissions(Permission::all());

        \Log::info("Permission 'QaFeedbackViewAll' created and assigned to the 'admin' role.");
    }
}
