<?php

namespace App\Jobs;

use Aws\Credentials\Credentials;
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

    /**
     * @throws \Exception
     */
    public function handle()
    {
        $outputFullPath = public_path('uploads/tmp/converted/' . $this->outputFileName);

        try {
            Log::info('Starting video conversion', [
                'inputFullPath' => $this->inputFullPath,
                'outputExtension' => $this->outputExtension,
                'outputFileName' => $this->outputFileName,
                'output' => $outputFullPath,
            ]);
            $this->convertVideo($this->inputFullPath, $outputFullPath);
            $this->uploadConvertedFile($outputFullPath);
        } catch (ProcessFailedException $e) {
            Log::error('Video conversion failed: ' . $e->getMessage());
            $this->cleanupFiles($outputFullPath);
            throw new \Exception('Video conversion failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('General error: ' . $e->getMessage());
            $this->cleanupFiles($outputFullPath);
            throw new \Exception('General error: ' . $e->getMessage());
        }
    }

    private function convertVideo($inputFullPath, $outputFullPath)
    {
        $timeout = 600;

        Log::info('Running video conversion', [
            'input' => $inputFullPath,
            'output' => $outputFullPath,
            'timeout ' => $timeout,
        ]);

        if (file_exists($outputFullPath)) {
            unlink($outputFullPath);
            Log::info('Deleted existing output file before conversion', [
                'output' => $outputFullPath,
            ]);
        }

        $startTime = microtime(true);

//        '-vf', 'scale=640:-1'
//        'cpulimit', '-l', '50', '--'
        $process = new Process([
            'ffmpeg',
            '-i', $inputFullPath,
            '-vcodec', 'libx264',
            '-crf', '30',
            '-preset', 'ultrafast',
            '-acodec', 'aac',
            '-b:a', '64k',
            '-threads', '2',
            $outputFullPath
        ]);

        $process->setWorkingDirectory(base_path());
        $process->setTimeout($timeout);

        Log::info('Test run!!!');

        $process->run(function ($type, $buffer) {
            if (Process::ERR === $type) {
                Log::error('FFmpeg error output: ' . $buffer);
            } else {
                Log::info('FFmpeg output: ' . $buffer);
            }
        });

        $endTime = microtime(true);

        $executionTime = $endTime - $startTime;

        if (!$process->isSuccessful()) {
            Log::error('Video conversion failed', [
                'input' => $inputFullPath,
                'output' => $outputFullPath,
                'error' => $process->getErrorOutput(),
            ]);

            if (file_exists($outputFullPath)) {
                unlink($outputFullPath);
            }

            throw new ProcessFailedException($process);
        }

        if (!file_exists($outputFullPath)) {
            Log::error('Output file not created', [
                'output' => $outputFullPath,
            ]);
            throw new \RuntimeException('Output file not created');
        }

        unlink($inputFullPath);

        Log::info('Video conversion completed successfully', [
            'deleted' => $inputFullPath,
            'output' => $outputFullPath,
            'execution_time' => $executionTime . ' seconds',
        ]);
    }

    private function uploadConvertedFile($outputFullPath)
    {
        Log::info('Uploading converted video', [
            'input' => $outputFullPath,
        ]);

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => env('AWS_ENDPOINT', 'https://s3.eu-central-003.backblazeb2.com'),
            'use_path_style_endpoint' => true,
            'signature_version' => 'v4',
            'credentials' => new Credentials(
                env('AWS_ACCESS_KEY_ID', '0037259dc1d2ab10000000001'),
                env('AWS_SECRET_ACCESS_KEY', 'K003dHlGPBcyyZuo3A1CdmrJHbOjyMk')
            ),
            'http' => ['verify' => false],
        ]);

        $result = $s3Client->putObject([
            'Bucket' => env('AWS_BUCKET', 'peaz-bucket'),
            'Key' => 'uploads/multipart/' . basename($outputFullPath),
            'SourceFile' => $outputFullPath,
            'ContentType' => 'video/' . $this->outputExtension,
        ]);

        Log::info('Upload of converted video completed successfully', [
            'Location' => $result['ObjectURL'],
            'Bucket' => env('AWS_BUCKET'),
            'Key' => 'uploads/multipart/' . basename($outputFullPath),
            'ETag' => $result['ETag'],
            'new_file_name' => basename($outputFullPath),
            'stored_in' => 'uploads/multipart',
            'deleted' => $outputFullPath,
        ]);

        if (file_exists($outputFullPath)) {
            unlink($outputFullPath);
            Log::info('Deleted output file after successful upload', [
                'output' => $outputFullPath,
            ]);
        }
    }

    private function cleanupFiles($outputFullPath)
    {
        if (file_exists($this->inputFullPath)) {
//            unlink($this->inputFullPath);
            Log::info('Deleted input file after error', [
                'input' => $this->inputFullPath,
            ]);
        }

        if (file_exists($outputFullPath)) {
//            unlink($outputFullPath);
            Log::info('Deleted output file after error', [
                'output' => $outputFullPath,
            ]);
        }
    }
}
