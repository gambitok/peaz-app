<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;
use Symfony\Component\Process\Exception\ProcessFailedException;
use Symfony\Component\Process\Process;
use Aws\S3\S3Client;

class ConvertVideo implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    private $inputFullPath;
    private $outputExtension;
    private $outputFileName;

    public function __construct($inputFullPath, $outputExtension, $outputFileName)
    {
        $this->inputFullPath = $inputFullPath;
        $this->outputExtension = $outputExtension;
        $this->outputFileName = $outputFileName;
    }

    public function handle()
    {
        $outputFullPath = storage_path('app/uploads/tmp/' . $this->outputFileName);

        try {
            $this->convertVideo($this->inputFullPath, $outputFullPath);
            $this->uploadConvertedFile($outputFullPath);
        } catch (ProcessFailedException $e) {
            Log::error('Video conversion failed: ' . $e->getMessage());
            throw new \Exception('Video conversion failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('General error: ' . $e->getMessage());
            throw new \Exception('General error: ' . $e->getMessage());
        }
    }

    private function convertVideo($inputFullPath, $outputFullPath)
    {
        $process = new Process(['ffmpeg', '-i', $inputFullPath, '-vcodec', 'libx264', '-crf', '28', '-preset', 'fast', $outputFullPath]);
        $process->setWorkingDirectory(base_path());
        $process->run();

        if (!$process->isSuccessful()) {
            throw new ProcessFailedException($process);
        }

        unlink($inputFullPath);

        Log::info('Video conversion completed successfully', [
            'input' => $inputFullPath,
            'output' => $outputFullPath,
        ]);
    }

    private function uploadConvertedFile($outputFullPath)
    {
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        $result = $s3Client->putObject([
            'Bucket' => env('AWS_BUCKET'),
            'Key' => 'uploads/converted/' . basename($outputFullPath),
            'SourceFile' => $outputFullPath,
            'ContentType' => 'video/' . $this->outputExtension,
        ]);

        unlink($outputFullPath);

        Log::info('Upload of converted video completed successfully', [
            'Location' => $result['ObjectURL'],
            'Bucket' => env('AWS_BUCKET'),
            'Key' => 'uploads/converted/' . basename($outputFullPath),
            'ETag' => $result['ETag'],
            'new_file_name' => basename($outputFullPath),
            'stored_in' => 'uploads/converted',
        ]);
    }
}
