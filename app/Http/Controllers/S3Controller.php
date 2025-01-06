<?php

namespace App\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use Symfony\Component\Process\Process;
use Symfony\Component\Process\Exception\ProcessFailedException;

class S3Controller extends Controller
{

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

//    public function convertVideo(Request $request)
//    {
//        // Check if the file exists
//        if (!$request->hasFile('video')) {
//            return response()->json(['message' => 'No video file uploaded'], 400);
//        }
//
//        // Get the file
//        $file = $request->file('video');
//        // Generate a unique identifier for the file name
//        $uniqueFileName = Str::uuid() . '.' . $file->getClientOriginalExtension();
//        // Store the file in the temporary directory with a unique name
//        $inputPath = $file->storeAs('tmp', $uniqueFileName, 'local');
//        $inputFullPath = storage_path('app/' . $inputPath); // Use the absolute path
//
//        // Determine the output format based on the uploaded file's extension
//        $outputExtension = $file->getClientOriginalExtension();
//        $outputFileName = Str::uuid() . '.' . $outputExtension;
//        $outputPath = 'public/' . $outputFileName;
//        $outputFullPath = storage_path('app/' . $outputPath); // Use the absolute path
//
//        // Define supported formats
//        $supportedFormats = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv'];
//
//        // Check if the output format is supported
//        if (!in_array($outputExtension, $supportedFormats)) {
//            return response()->json(['message' => 'Unsupported file format'], 400);
//        }
//
//        try {
//            // Perform the conversion using FFMpeg
//            // Parameters -vcodec and -crf configure the video codec and compression rate factor respectively
//            $process = new Process(['ffmpeg', '-i', $inputFullPath, '-vcodec', 'libx264', '-crf', '28', '-preset', 'fast', $outputFullPath]);
//            $process->setWorkingDirectory(base_path()); // Set the working directory
//            $process->run();
//
//            // Check for errors during the process
//            if (!$process->isSuccessful()) {
//                throw new ProcessFailedException($process);
//            }
//
//            // Remove the temporary file
//            unlink($inputFullPath);
//
//            return response()->json(['message' => 'Video successfully compressed', 'path' => $outputPath]);
//        } catch (ProcessFailedException $e) {
//            // Handle errors during the process execution
//            return response()->json([
//                'message' => 'Video conversion failed',
//                'error' => $e->getMessage(),
//                'command' => $e->getProcess()->getCommandLine(),
//                'exitCode' => $e->getProcess()->getExitCode(),
//                'output' => $e->getProcess()->getOutput(),
//                'errorOutput' => $e->getProcess()->getErrorOutput(),
//            ], 500);
//        } catch (\Exception $e) {
//            // Handle general errors
//            return response()->json(['message' => 'General error: ' . $e->getMessage()], 500);
//        }
//    }

    private $supportedFormats = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv'];

    public function convertVideo($inputFullPath, $outputExtension)
    {
        $outputFileName = Str::uuid() . '.' . $outputExtension;
        $outputFullPath = storage_path('app/uploads/tmp/' . $outputFileName);

        try {
            // Perform the conversion using FFMpeg
            $process = new Process(['ffmpeg', '-i', $inputFullPath, '-vcodec', 'libx264', '-crf', '28', '-preset', 'fast', $outputFullPath]);
            $process->setWorkingDirectory(base_path());
            $process->run();

            // Check for errors during the process
            if (!$process->isSuccessful()) {
                throw new ProcessFailedException($process);
            }

            // Remove the temporary input file
            unlink($inputFullPath);

            return $outputFullPath;
        } catch (ProcessFailedException $e) {
            Log::error('Video conversion failed: ' . $e->getMessage());
            throw new \Exception('Video conversion failed: ' . $e->getMessage());
        } catch (\Exception $e) {
            Log::error('General error: ' . $e->getMessage());
            throw new \Exception('General error: ' . $e->getMessage());
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

//    public function completeMultipartUpload(Request $request)
//    {
//        $fileName = $request->file_name;
//        $uploadId = $request->upload_id;
//        $parts = $request->parts; // Array of parts with ETag and PartNumber
//
//        $s3Client = new S3Client([
//            'version' => 'latest',
//            'region' => env('AWS_DEFAULT_REGION'),
//            'credentials' => [
//                'key' => env('AWS_ACCESS_KEY_ID'),
//                'secret' => env('AWS_SECRET_ACCESS_KEY'),
//            ],
//        ]);
//
//        try {
//            $result = $s3Client->completeMultipartUpload([
//                'Bucket' => env('AWS_BUCKET'),
//                'Key' => 'uploads/multipart/' . $fileName,
//                'UploadId' => $uploadId,
//                'MultipartUpload' => [
//                    'Parts' => $parts,
//                ],
//            ]);
//
//            return response()->json(['message' => 'Upload completed successfully', 'result' => $result]);
//        } catch (\Exception $e) {
//            return response()->json(['error' => $e->getMessage()], 500);
//        }
//    }

    public function completeMultipartUpload(Request $request)
    {
        $fileName = $request->file_name;
        $uploadId = $request->upload_id;
        $parts = $request->parts;

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        try {
            // Complete the multipart upload
            $result = $s3Client->completeMultipartUpload([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => 'uploads/multipart/' . $fileName,
                'UploadId' => $uploadId,
                'MultipartUpload' => [
                    'Parts' => $parts,
                ],
            ]);

            // Get the uploaded file from S3
            $s3Client->getObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => 'uploads/multipart/' . $fileName,
                'SaveAs' => storage_path('app/uploads/tmp/' . $fileName),
            ]);

            // Determine the input file path and output extension
            $inputFullPath = storage_path('app/uploads/tmp/' . $fileName);
            $outputExtension = pathinfo($fileName, PATHINFO_EXTENSION);

            // Log the detected file extension for debugging
            Log::info('Detected file extension: ' . $outputExtension);

            // Check if the format is supported
            if (in_array($outputExtension, $this->supportedFormats)) {
                // Convert the video
                $convertedFilePath = $this->convertVideo($inputFullPath, $outputExtension);

                // Upload the converted video back to S3
                $result = $s3Client->putObject([
                    'Bucket' => env('AWS_BUCKET'),
                    'Key' => 'uploads/converted/' . basename($convertedFilePath),
                    'SourceFile' => $convertedFilePath,
                    'ContentType' => 'video/' . $outputExtension,
                ]);

                // Remove the temporary converted file
                unlink($convertedFilePath);

                return response()->json([
                    'message' => 'Upload and conversion completed successfully',
                    'result' => [
                        'Location' => $result['ObjectURL'],
                        'Bucket' => env('AWS_BUCKET'),
                        'Key' => 'uploads/converted/' . basename($convertedFilePath),
                        'ETag' => $result['ETag'],
                        'new_file_name' => basename($convertedFilePath),
                        'stored_in' => 'uploads/converted'
                    ],
                ]);
            } else {
                // Log that the format is not supported
                Log::info('Format not supported: ' . $outputExtension);

                // If the format is not supported, just return the original file's information
                return response()->json([
                    'message' => 'Upload completed, but conversion was not performed because the format is not supported',
                    'result' => [
                        'Location' => $s3Client->getObjectUrl(env('AWS_BUCKET'), 'uploads/multipart/' . $fileName),
                        'Bucket' => env('AWS_BUCKET'),
                        'Key' => 'uploads/multipart/' . $fileName,
                        'ETag' => $result['ETag'],
                        'new_file_name' => $fileName,
                        'stored_in' => 'uploads/multipart'
                    ],
                ]);
            }
        } catch (\Exception $e) {
            Log::error('Error in completeMultipartUpload: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
