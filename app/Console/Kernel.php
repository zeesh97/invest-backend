<?php

namespace App\Console;

use App\Jobs\SendApprovalEmailJob;
use App\Services\Core\Tasks\BackupCleanupService;
use Illuminate\Console\Scheduling\Schedule;
use Illuminate\Foundation\Console\Kernel as ConsoleKernel;
use Illuminate\Support\Facades\DB;
use Log;
use Spatie\Backup\Events\CleanupHasFailed;
use Throwable;

class Kernel extends ConsoleKernel
{
    /**
     * Define the application's command schedule.
     */
    protected function schedule(Schedule $schedule): void
    {
        $this->scheduleBackup($schedule);
        $this->scheduleCleanup($schedule);

        $this->scheduleCheckSubscriptionExpired($schedule);
        $this->scheduleCleanupExpiredImpersonation($schedule);
        $schedule->command('sessions:clear')->daily();
    }


    /**
     * Schedules the backup process.
     */
    protected function scheduleBackup(Schedule $schedule): void
    {
        $schedule->command('backup:run')
            ->dailyAt('01:30')
            ->onSuccess(function () {
                Log::info('Daily backup successful.');
            })
            ->onFailure(function () {
                Log::error('Daily backup failed!');
            });
    }


    /**
     * Schedules the backup cleanup process.
     */
    protected function scheduleCleanup(Schedule $schedule): void
    {
        $schedule->command('backup:clean')
            ->dailyAt('01:35')
            ->onSuccess(function () {
                Log::info('Backup cleaning successful.');
                $backups = new BackupCleanupService;
                $backups->deleteOldBackups(); // Consider moving this to a dedicated command
            })
            ->onFailure(function (Throwable $e) {
                Log::error('Backup cleanup failed.'); // More generic message
                event(new CleanupHasFailed($e));
                Log::error("Cleanup failed: {$e->getMessage()}");
            });
    }

    protected function scheduleCleanupExpiredImpersonation(Schedule $schedule): void
    {
        $schedule->call(function () {
            try {
                DB::table('impersonations')
                    ->where(function ($query) {
                        $query->where('expires_at', '<', now())
                            ->orWhereNotNull('ended_at');
                    })
                    ->delete();
            } catch (Throwable $e) {
                Log::error("Impersonation user records remove failed: " . $e->getMessage());
            }
        })->everyMinute();
    }


    /**
     * Schedules sending approval emails.
     */
    protected function scheduleCheckSubscriptionExpired(Schedule $schedule): void
    {
        $schedule->call(function () {
            try {
                DB::table('subscriptions')
                    ->where('end_date', '<', now())->whereNull('expired_at')
                    ->update(['expired_at' => now()]);
            } catch (Throwable $e) {
                Log::error("Subscription expiry update failed: " . $e->getMessage());
            }
        })->everyMinute();
    }


    /**
     * Register the commands for the application.
     */
    protected function commands(): void
    {
        $this->load(__DIR__ . '/Commands');

        require base_path('routes/console.php');
    }
}
