<?php

namespace App\Services\Core\Tasks;

use App\Http\Helpers\Helper;
use Carbon\Carbon;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Notification;
use Spatie\Backup\BackupDestination\BackupCollection;
use Spatie\Backup\BackupDestination\BackupDestination;
use App\Notifications\OldBackupsDeletedNotification;
use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Storage;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;

class BackupCleanupService
{
    private const RETENTION_DAYS = 7;

    public function deleteOldBackups(): JsonResponse
    {
        $backupDestinations = BackupDestinationFactory::createFromArray(config('backup.backup'));
        $sevenDaysAgo = Carbon::now()->subDays(self::RETENTION_DAYS);
        $deletedCount = 0;

        $backupDestinations->each(function ($backupDestination) use ($sevenDaysAgo, &$deletedCount) {
            $backupDestination->backups()->each(function ($backup) use ($backupDestination, $sevenDaysAgo, &$deletedCount) {
                $filePath = $backup->path();

                // Check if the backup is older than 7 days
                if ($backup->date()->lessThan($sevenDaysAgo)) {
                    // Verify if the backup file exists before deleting
                    if (Storage::disk($backupDestination->diskName())->exists($filePath)) {
                        try {
                            // Delete the backup file
                            Storage::disk($backupDestination->diskName())->delete($filePath);
                            Log::info("Deleted old backup: {$filePath}");
                            $deletedCount++;
                        } catch (Exception $exception) {
                            Log::error("Failed to delete backup: {$filePath}", ['error' => $exception->getMessage()]);
                        }
                    } else {
                        Log::warning("Backup file {$filePath} not found.");
                    }
                }
            });
        });

        // Send notification with count of deleted backups
        if ($deletedCount > 0) {
            $this->sendDeletionNotification($deletedCount);
        }

        return Helper::sendResponse(['deleted_count' => $deletedCount], 'Old backups deleted successfully', 200);
    }


    protected function sendDeletionNotification(int $count): void
    {
        // Send the notification to a predefined channel or user
        Notification::route('mail', 'ahsannajamkhan@gmail.com')
            ->notify(new OldBackupsDeletedNotification($count));
    }
}
