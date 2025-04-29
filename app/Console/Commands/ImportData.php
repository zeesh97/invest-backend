<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class ImportData extends Command
{
    protected $signature = 'import:data';

    protected $description = 'Import teams, departments, and designations';


    public function handle()
    {
        $teams = [
            "Logistics",
            "Store",
            "Planning & Maintenance",
            "GM Secretariat",
            "Power Generation",
            "Admin",
            "Marketing",
            "Weighbridge",
            "Finance & Accounts",
            "Export",
            "Accounts",
            "Dispatch",
            "EXPORT DOCUMENTATION",
            "Administration",
            "Main Gate & Weigh Bridge",
            "ITs",
            "Supply Chain",
            "Coordination",
            "COMPLIANCE",
            "Coal",
            "Admin & Security",
            "Travel",
            "Treasury",
            "Financial Reporting",
            "Accounts Payable",
            "Finance Department",
            "Costing & Budgeting",
            "Tax",
            "Production",
            "PHR",
            "Human Resource & Admin"
        ];

        $sections = [
            "Logistics",
            "Store",
            "Planning & Maintenance",
            "GM Secretariat",
            "Power Generation",
            "Admin",
            "Marketing",
            "Weighbridge",
            "Finance & Accounts",
            "Export",
            "Accounts",
            "Dispatch",
            "EXPORT DOCUMENTATION",
            "Administration",
            "Main Gate & Weigh Bridge",
            "ITs",
            "Supply Chain",
            "Coordination",
            "COMPLIANCE",
            "Coal",
            "Admin & Security",
            "Travel",
            "Treasury",
            "Financial Reporting",
            "Accounts Payable",
            "Finance Department",
            "Costing & Budgeting",
            "Tax",
            "Production",
            "PHR",
            "Human Resource & Admin"
        ];

        $departments = [
            "Logistics",
            "Store",
            "Planning & Maintenance",
            "GM Secretariat",
            "Power Generation",
            "Admin",
            "Marketing",
            "Weighbridge",
            "Finance & Accounts",
            "Export",
            "Accounts",
            "Dispatch",
            "EXPORT DOCUMENTATION",
            "Administration",
            "Main Gate & Weigh Bridge",
            "ITs",
            "Supply Chain",
            "Coordination",
            "COMPLIANCE",
            "Coal",
            "Admin & Security",
            "Travel",
            "Treasury",
            "Financial Reporting",
            "Accounts Payable",
            "Finance Department",
            "Costing & Budgeting",
            "Tax",
            "Production",
            "PHR",
            "Human Resource & Admin"
        ];

        $designations = [
            "Deputy Manager",
            "General Manager",
            "Senior Deputy Manager",
            "Deputy General Manager",
            "Manager",
            "Senior Assistant manager",
            "Senior Manager",
            "Assistant Manager",
            "Senior Officer",
            "Compliance Specialist"
        ];

        $locations = [
            "LCKP",
            "LCKP/LCPZ",
            "LCPZ",
            "PGKP",
            "PGPZ",
            "CMO",
            "LCHO",
        ];

        $companies = ['LCL'];

        try {
            DB::transaction(function () use ($teams, $sections, $departments, $designations, $locations, $companies) {
                $this->insertIfNotExists('teams', $teams);
                $this->insertIfNotExists('sections', $sections);
                $this->insertIfNotExists('departments', $departments);
                $this->insertIfNotExists('designations', $designations);
                $this->insertIfNotExists('locations', $locations);
                $this->insertIfNotExists('companies', $companies);
            });

            $this->info("Data imported successfully!");
        } catch (\Exception $e) {
            $this->error("An error occurred: " . $e->getMessage());
        }
    }

    private function insertIfNotExists($table, $data)
    {
        foreach ($data as $item) {
            if ($table == 'locations' || $table == 'companies') {
                $formattedItem = $item;
            } else {
                $formattedItem = $this->titleCase($item);
            }
            $exists = DB::table($table)->where('name', $formattedItem)->exists();

            if (!$exists) {
                DB::table($table)->insert(['name' => $formattedItem]);
            }
        }
    }

    private function titleCase($string)
    {
        return ucwords(strtolower($string));
    }
}
