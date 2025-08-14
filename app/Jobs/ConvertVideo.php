<?php

namespace App\Jobs;

use App\Post;
use App\PostThumbnail;
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
    private $postId;
    private $postThumbnailId;
    private $isThumbnail;


    public function __construct($inputFullPath, $outputExtension, $outputFileName, $postId = null, $postThumbnailId = null, $isThumbnail = false)
    {
        $this->inputFullPath = $inputFullPath;
        $this->outputExtension = $outputExtension;
        $this->outputFileName = $outputFileName;
        $this->postId = $postId;
        $this->postThumbnailId = $postThumbnailId;
        $this->isThumbnail = $isThumbnail;

        if ($this->postId && !$this->postThumbnailId) {
            $post = Post::find($this->postId);
            if ($post) {
                $post->update(['conversion_status' => 'processing']);
            }
        }
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

            if ($this->postId && !$this->postThumbnailId) {
                $post = Post::find($this->postId);
                if ($post) {
                    $post->update(['conversion_status' => 'completed']);
                }
            }
        } catch (ProcessFailedException $e) {
            Log::error('Video conversion failed: ' . $e->getMessage());
            $this->updateStatusFailed();
            $this->cleanupFiles($outputFullPath);
            throw new \Exception('Video conversion failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('General error: ' . $e->getMessage());
            $this->updateStatusFailed();
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

        $key = 'uploads/multipart/' . basename($outputFullPath);

        $result = $s3Client->putObject([
            'Bucket' => env('AWS_BUCKET', 'peaz-bucket'),
            'Key' => $key,
            'SourceFile' => $outputFullPath,
            'ContentType' => 'video/' . $this->outputExtension,
        ]);

        Log::info('Upload completed', [
            'Location' => $result['ObjectURL'],
            'Key' => $key,
        ]);

        $post = Post::find($this->postId);
        if ($post) {
            $savePath = $result['ObjectURL'];

            if ($this->postThumbnailId) {
                $thumbnail = PostThumbnail::find($this->postThumbnailId);
                if ($thumbnail) {
                    $thumbnail->thumbnail = $savePath;
                    $thumbnail->save();
                    Log::info("PostThumbnail ID {$this->postThumbnailId} updated with new thumbnail path: {$savePath}");
                }
            } else {
                if ($this->isThumbnail) {
                    $post->thumbnail = $savePath;
                    Log::info("Post ID {$this->postId} updated with new thumbnail path: {$savePath}");
                } else {
                    $post->file = $savePath;
                    Log::info("Post ID {$this->postId} updated with new file path: {$savePath}");
                }
                $post->save();
            }
        }

        if (file_exists($outputFullPath)) {
            unlink($outputFullPath);
            Log::info('Deleted output file after upload', [
                'output' => $outputFullPath,
            ]);
        }
    }

    private function updateStatusFailed()
    {
        if ($this->postId && !$this->postThumbnailId) {
            $post = Post::find($this->postId);
            if ($post) {
                $post->update(['conversion_status' => 'failed']);
                Log::warning("Post ID {$this->postId} updated with status: failed");
            }
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
