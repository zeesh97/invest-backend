<?php

namespace App\Console\Commands;

use App\Models\Company;
use App\Models\Department;
use App\Models\Designation;
use App\Models\Location;
use App\Models\Section;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use App\Models\User;
use Carbon\Carbon;
use Spatie\Permission\Models\Role;

class ImportUsers extends Command
{
    protected $signature = 'import:users';
    protected $description = 'Import users from CSV';

    public function handle()
    {
        // Path to your CSV file
        $csvFile = base_path() . '/public/updated_import_users.csv';

        if (!file_exists($csvFile)) {
            $this->error("CSV file not found: " . $csvFile);
            return;
        }

        try {
            $file = fopen($csvFile, 'r');
            fgetcsv($file); // Skip header row

            DB::beginTransaction();

            $usersToInsert = []; // Array to hold users for bulk insert

            while (($data = fgetcsv($file)) !== false) {
                $email = $data[1];

                // Check if user already exists
                if (!User::where('email', $email)->exists()) {
                    $usersToInsert[] = $this->createUserFromArray($data);
                }

                // Insert in chunks of 1000 for performance
                if (count($usersToInsert) >= 1000) {
                    User::insert($usersToInsert);
                    $usersToInsert = []; // Reset array
                }
            }

            // Insert any remaining users
            if (!empty($usersToInsert)) {
                User::insert($usersToInsert);
            }

            // Assign roles to newly inserted users
            $emails = array_column($usersToInsert, 'email');
            $users = User::whereIn('email', $emails)->get();

            $userRole = Role::findByName('user');
            if ($userRole) {
                $users->each(function ($user) use ($userRole) {
                    $user->assignRole($userRole);
                });
            } else {
                $this->warn("Role 'user' not found. Users may not have roles assigned.");
            }

            DB::commit();
            fclose($file);
            $this->info("Users imported successfully!");

        } catch (\Exception $e) {
            DB::rollBack();
            fclose($file);
            $this->error("An error occurred: " . $e->getMessage());
        }
    }

    private function createUserFromArray($data)
    {
        return [
            'name' => $this->sanitizeName($data[0]),
            'email' => $data[1],
            'password' => bcrypt('Abcd1234'), // Hash password explicitly
            'employee_no' => $data[2],
            'employee_type' => 'Permanent',
            'designation_id' => Designation::firstOrCreate(['name' => $this->titleCase($data[3])])->id,
            'department_id' => Department::firstOrCreate(['name' => $this->titleCase($data[4])])->id,
            'location_id' => Location::firstOrCreate(['name' => $this->titleCase($data[5])])->id,
            'section_id' => Section::firstOrCreate([
                'name' => $this->sanitizeName($this->titleCase($data[6])),
                'department_id' => Department::firstOrCreate(['name' => $this->titleCase($data[4])])->id,
                ])->id,
                'company_id' => Company::firstOrCreate(['name' => $this->titleCase($data[7])])->id,
            'created_at' => now(),
        ];
    }

    private function titleCase($string)
    {
        return mb_convert_case($string, MB_CASE_TITLE, "UTF-8");
    }

    private function sanitizeName($name)
    {
        // Remove non-breaking spaces and control characters
        $name = preg_replace('/[\x00-\x1F\x7F-\xA0]/u', '', $name);
        return trim($name);
    }
}
