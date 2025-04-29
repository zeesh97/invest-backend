<?php

namespace App\Console\Commands;

use Carbon\Carbon;
use DB;
use Illuminate\Console\Command;

class ClearExpiredSessions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sessions:clear';
    protected $description = 'Clear expired session entries';

    public function handle()
    {
        try {
            $lifetime = config('session.lifetime');

            if ($lifetime === null) {
                $this->error("Session lifetime is not configured.");
                return 1;
            }

            $expiredAt = Carbon::now()->subMinutes($lifetime)->timestamp;


            $deletedSessions = DB::table('sessions')
                ->where('last_activity', '<', $expiredAt)
                ->delete();


            $this->info("Cleared {$deletedSessions} expired session entries.");


        } catch (\Throwable $e) {
            $this->error("Error clearing expired sessions: " . $e->getMessage());
            \Log::error($e);
            return 1;
        }

        return 0;
    }
}
