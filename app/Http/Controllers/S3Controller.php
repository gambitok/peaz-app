<?php

namespace App\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class S3Controller extends Controller
{

    public function convertVideo(Request $request)
    {
        // Перевірка наявності файлу
        if (!$request->hasFile('video')) {
            return response()->json(['message' => 'No video file uploaded'], 400);
        }

        // Отримання файлу
        $file = $request->file('video');
        // Генерація унікального ідентифікатора для назви файлу
        $uniqueFileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
        // Зберігання файлу в тимчасовій директорії з унікальною назвою
        $inputPath = $file->storeAs('tmp', $uniqueFileName, 'local');
        $inputFullPath = storage_path('app/' . $inputPath); // Використання абсолютного шляху
        $outputFileName = Str::uuid() . '.mp4';
        $outputPath = 'public/' . $outputFileName;
        $outputFullPath = storage_path('app/' . $outputPath); // Використання абсолютного шляху

        try {
            // Виконання конвертації за допомогою FFMpeg
            $process = new Process(['ffmpeg', '-i', $inputFullPath, $outputFullPath]);
            $process->setWorkingDirectory(base_path()); // Встановлення робочої директорії
            $process->run();

            // Перевірка на помилки під час процесу
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Видалення тимчасового файлу
            unlink($inputFullPath);

            return response()->json(['message' => 'Video successfully converted', 'path' => $outputPath]);
        } catch (ProcessFailedException $e) {
            // Обробка помилки під час виконання процесу
            return response()->json([
                'message' => 'Video conversion failed',
                'error' => $e->getMessage(),
                'command' => $e->getProcess()->getCommandLine(),
                'exitCode' => $e->getProcess()->getExitCode(),
                'output' => $e->getProcess()->getOutput(),
                'errorOutput' => $e->getProcess()->getErrorOutput(),
            ], 500);
        } catch (\Exception $e) {
            // Обробка загальних помилок
            return response()->json(['message' => 'General error: ' . $e->getMessage()], 500);
        }
    }

    public function generatePresignedUrl(Request $request)
    {
        // Get the operation (PUT, GET, DELETE, etc.)
        $operation = strtoupper($request->operation); // e.g., 'PUT', 'GET', 'DELETE'

        // Get the file name and folder type from the request
        $folder = $request->folder; // e.g., 'users', 'posts', 'posts/thumbnails'
        $fileName = $request->file_name; // e.g., 'image.jpg'

        // Define allowed extensions
        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];

        // Validate folder input
        if ($folder === 'users') {
            // Validate file extension for images only
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($extension, $imageExtensions)) {
                return response()->json(['error' => 'Invalid file extension for users. Only image types are allowed (jpg, png, etc.).'], 400);
            }
            $filePath = 'uploads/users/' . $fileName;
        } elseif ($folder === 'posts') {
            // Posts can also be images or videos
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (in_array($extension, $imageExtensions)) {
                $fileType = 'images';
            } elseif (in_array($extension, $videoExtensions)) {
                $fileType = 'videos';
            } else {
                return response()->json(['error' => 'Invalid file extension. Allowed types are: images (jpg, png, etc.) and videos (mp4, mov, etc.).'], 400);
            }

            $filePath = 'uploads/posts/' . $fileType . '/' . $fileName;
        } elseif ($folder === 'posts/thumbnails') {
            // Thumbnails can also be images or videos
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (in_array($extension, $imageExtensions)) {
                $fileType = 'images';
            } elseif (in_array($extension, $videoExtensions)) {
                $fileType = 'videos';
            } else {
                return response()->json(['error' => 'Invalid file extension. Allowed types are: images (jpg, png, etc.) and videos (mp4, mov, etc.).'], 400);
            }

            $filePath = 'uploads/posts/thumbnails/' . $fileType . '/' . $fileName;
        } else {
            return response()->json(['error' => 'Invalid folder specified.'], 400);
        }

        // Set up the AWS S3 client
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        // Select the appropriate command based on the operation
        try {
            if ($operation === 'PUT') {
                $command = $s3Client->getCommand('PutObject', [
                    'Bucket' => env('AWS_BUCKET'),
                    'Key' => $filePath,
                ]);
            } elseif ($operation === 'GET') {
                $command = $s3Client->getCommand('GetObject', [
                    'Bucket' => env('AWS_BUCKET'),
                    'Key' => $filePath,
                ]);
            } elseif ($operation === 'DELETE') {
                $command = $s3Client->getCommand('DeleteObject', [
                    'Bucket' => env('AWS_BUCKET'),
                    'Key' => $filePath,
                ]);
            } else {
                return response()->json(['error' => 'Invalid operation type.'], 400);
            }

            // Generate the presigned URL
            $presignedRequest = $s3Client->createPresignedRequest($command, '+10 minutes');

            // Get the presigned URL as a string
            $presignedUrl = (string)$presignedRequest->getUri();

            // Return the presigned URL
            return response()->json(['url' => $presignedUrl]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function getMimeTypeFromExtension($extension, $mapping) {
        return isset($mapping[$extension]) ? $mapping[$extension] : 'application/octet-stream';
    }

    public function initiateMultipartUpload(Request $request)
    {
        $fileName = $request->file_name; // e.g., 'large_video.mp4'

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];
        $videoExtensionsToMimeTypes = [
            'mp4' => 'video/mp4',
            'avi' => 'video/x-msvideo',
            'mov' => 'video/quicktime',
            'mkv' => 'video/x-matroska',
            'wmv' => 'video/x-ms-wmv',
            'flv' => 'video/x-flv',
        ];
        $mimeType = $this->getMimeTypeFromExtension($extension, $videoExtensionsToMimeTypes);

        if (!in_array($extension, $videoExtensions)) {
            return response()->json(['error' => 'Invalid file extension for users. Only image types are allowed (mp4, mov, etc.).'], 400);
        }

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        try {
            $result = $s3Client->createMultipartUpload([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => 'uploads/multipart/' . $fileName,
                'ContentType' => $mimeType,
            ]);

            return response()->json(['upload_id' => $result['UploadId']]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generateMultipartPresignedUrl(Request $request)
    {
        $fileName = $request->file_name; // e.g., 'large_video.mp4'
        $partNumber = $request->part_number; // Part number for the multipart upload
        $uploadId = $request->upload_id; // Upload ID for the multipart upload

        // Define allowed video extensions
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        if (!in_array($extension, $videoExtensions)) {
            return response()->json(['error' => 'Invalid file extension. Allowed types are: videos (mp4, mov, etc.).'], 400);
        }

        $filePath = 'uploads/multipart/' . $fileName;

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        try {
            $command = $s3Client->getCommand('UploadPart', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $filePath,
                'PartNumber' => $partNumber,
                'UploadId' => $uploadId,
            ]);

            $presignedRequest = $s3Client->createPresignedRequest($command, '+10 minutes');
            $presignedUrl = (string)$presignedRequest->getUri();

            return response()->json(['url' => $presignedUrl]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function completeMultipartUpload(Request $request)
    {
        $fileName = $request->file_name;
        $uploadId = $request->upload_id;
        $parts = $request->parts; // Array of parts with ETag and PartNumber

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        try {
            $result = $s3Client->completeMultipartUpload([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => 'uploads/multipart/' . $fileName,
                'UploadId' => $uploadId,
                'MultipartUpload' => [
                    'Parts' => $parts,
                ],
            ]);

            return response()->json(['message' => 'Upload completed successfully', 'result' => $result]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
