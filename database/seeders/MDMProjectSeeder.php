<?php

namespace Database\Seeders;

use App\Models\Form;
use App\Models\ProjectMDM;
use App\Models\SetupField;
use Carbon\Carbon;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class MDMProjectSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $permissions = [
            'ProjectMDM-view',
            'ProjectMDM-create',
            'ProjectMDM-edit',
            'ProjectMDM-delete',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission]);
        }

        $adminRole = Role::where('name', 'admin')->first();
        if ($adminRole) {
            $adminRole->givePermissionTo($permissions);
        }

        SetupField::firstOrCreate(
            ['id' => 7],
            [
                'name' => 'Project MDM',
                'identity' => "App\\Models\\ProjectMDM",
                'created_at' => Carbon::now(),
                'updated_at' => Carbon::now(),
            ]
        );
    }
}
