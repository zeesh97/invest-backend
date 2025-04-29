<?php

namespace App\Http\Controllers;

use App\Models\Backup;
use Illuminate\Http\JsonResponse;
use App\Http\Helpers\Helper;
use Log;
use Spatie\Backup\BackupDestination\BackupDestination;
use Spatie\Backup\BackupDestination\BackupDestinationFactory;
use Storage;

use Google\Client;
use Google\Service\Drive;

// use Google_Client;
// use Google_Service_Drive;
class BackupController extends Controller
{
    protected $client;

    public function __construct()
    {
        $this->client = new Client();
        $this->client->useApplicationDefaultCredentials();
        $this->client->addScope(Drive::DRIVE);
    }
    // public function downloadBackup($file)
    // {
    //     if(Storage::disk('google')->exists($file)){

    //         $response = Storage::disk('google')->download($file);

    //         $driveService = new Drive($this->client);
    //         // dd($driveService->files);
    //         // Fetch file content
    //         // $response = $driveService->files->get($fileId, ['alt' => 'media']);
    //         return $response;
    //     }
    //     $filePath = env('APP_NAME') . "/{$file}";
    //     if (Storage::disk('local')->exists($filePath)) {
    //         return response()->download("https://drive.google.com/drive/u/4/folders/1hZQYVxx7tih9RG0Eske4cJirUI_QfcKF/".$filePath);
    //     }
    //     return response()->json(['message' => 'Backup file not found'], 404);
    // }
    public function getBackups(): JsonResponse
    {
        try {
            $this->client->setClientId(env('GOOGLE_DRIVE_CLIENT_ID'));
            $this->client->setClientSecret(env('GOOGLE_DRIVE_CLIENT_SECRET'));
            $this->client->refreshToken(env('GOOGLE_DRIVE_REFRESH_TOKEN'));
            $this->client->setScopes([Drive::DRIVE_READONLY]);
            $this->client->setAccessType('offline');

            $service = new Drive($this->client);
            $folderId = env('GOOGLE_DRIVE_FOLDER');

            $parameters = [
                'q' => "'$folderId' in parents",
                'fields' => 'files(id, name, webContentLink, webViewLink)',
            ];
            $results = $service->files->listFiles($parameters);

            $files = $results->getFiles();

            if (empty($files)) {
                return response()->json(['message' => 'No files found in the folder.'], 200);
            }

            $fileList = [];
            foreach ($files as $file) {
                $fileList[] = [
                    'name' => $file->getName(),
                    'download_url' => $file->getWebContentLink(),
                    'view_url' => $file->getWebViewLink(),
                ];
            }

            return response()->json(['files' => $fileList], 200);
        } catch (\Exception $e) {
            return response()->json(['error' => 'Failed to retrieve files: ' . $e->getMessage()], 500);
        }
    }
}
