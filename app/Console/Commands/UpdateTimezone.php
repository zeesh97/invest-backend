<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;
use Schema;

class UpdateTimezone extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'timezone:update {table} {column}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update timestamps from UTC to Asia/Karachi';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $table = $this->argument('table');
        $column = $this->argument('column');

        // Validate table and column existence (optional but recommended)
        if (!Schema::hasTable($table) || !Schema::hasColumn($table, $column)) {
            $this->error("Table or column doesn't exist.");
            return;
        }


        // Fetch all records with UTC timestamps
        $records = DB::table($table)->whereNotNull($column)->get();

        $karachiTimezone = 'Asia/Karachi';
        $updatedCount = 0;

        foreach ($records as $record) {
            try {
                // Convert UTC timestamp to Carbon object
                $utcTimestamp = Carbon::parse($record->$column, 'UTC');  // Important: Specify UTC

                // Convert to Karachi time
                $karachiTime = $utcTimestamp->copy()->setTimezone($karachiTimezone);

                 // Update the record
                DB::table($table)
                    ->where('id', $record->id) // Assumes you have an 'id' column. Adjust accordingly
                    ->update([$column => $karachiTime]);

                $updatedCount++;

            } catch (\Exception $e) {
                $this->error("Error updating record with id {$record->id}: " . $e->getMessage());
                //  Handle the error appropriately.  Log it, skip it, etc.
            }
        }

        $this->info("Updated {$updatedCount} records in table '{$table}' from UTC to Asia/Karachi.");
    }
}
