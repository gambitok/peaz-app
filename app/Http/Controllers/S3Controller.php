<?php

namespace App\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Jobs\ConvertVideo;

class S3Controller extends Controller
{

    public function generatePresignedUrl(Request $request)
    {
        $operation = strtoupper($request->operation);

        $folder = $request->folder;
        $fileName = $request->file_name;

        $imageExtensions = ['jpg', 'jpeg', 'png', 'gif', 'bmp', 'webp'];
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];

        if ($folder === 'users') {
            $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));

            if (!in_array($extension, $imageExtensions)) {
                return response()->json(['error' => 'Invalid file extension for users. Only image types are allowed (jpg, png, etc.).'], 400);
            }
            $filePath = 'uploads/users/' . $fileName;
        } elseif ($folder === 'posts') {
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

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key' => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

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

            $presignedRequest = $s3Client->createPresignedRequest($command, '+10 minutes');

            $presignedUrl = (string)$presignedRequest->getUri();

            return response()->json(['url' => $presignedUrl]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    private $supportedFormats = ['mp4', 'avi', 'mov', 'mkv', 'flv', 'wmv'];

    public function getMimeTypeFromExtension($extension, $mapping) {
        return isset($mapping[$extension]) ? $mapping[$extension] : 'application/octet-stream';
    }

    public function initiateMultipartUpload(Request $request)
    {
        $fileName = $request->file_name;

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
            return response()->json(['error' => 'Invalid file extension for users. Only video types are allowed (mp4, mov, etc.).'], 400);
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
        $fileName = $request->file_name;
        $partNumber = $request->part_number;
        $uploadId = $request->upload_id;

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
        $originalFileName = $request->input('file_name');
        $uploadId = $request->input('upload_id');
        $parts = $request->input('parts');

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
                'Key' => 'uploads/multipart/' . $originalFileName,
                'UploadId' => $uploadId,
                'MultipartUpload' => [
                    'Parts' => $parts,
                ],
            ]);

            $inputFullPath = public_path('uploads/tmp/' . $originalFileName);
            $s3Client->getObject([
                'Bucket' => env('AWS_BUCKET'),
                'Key' => 'uploads/multipart/' . $originalFileName,
                'SaveAs' => $inputFullPath,
            ]);

            $outputExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);
            $newFileName = Str::uuid() . '.' . $outputExtension;

            if (in_array($outputExtension, $this->supportedFormats)) {
                Log::info('Dispatching ConvertVideo job', [
                    'inputFullPath' => $inputFullPath,
                    'outputExtension' => $outputExtension,
                    'outputFileName' => $newFileName,
                ]);
                ConvertVideo::dispatch($inputFullPath, $outputExtension, $newFileName);
                $compressionStatus = 'Compression started';
            } else {
                Log::info('Format not supported: ' . $outputExtension);
                $compressionStatus = 'Compression not supported';
            }

            return response()->json([
                'message' => 'Upload completed successfully',
                'compression_status' => $compressionStatus,
                'result' => [
                    'Location' => $s3Client->getObjectUrl(env('AWS_BUCKET'), 'uploads/multipart/' . $newFileName),
                    'Bucket' => env('AWS_BUCKET'),
                    'Key' => 'uploads/multipart/' . $originalFileName,
                    'ETag' => $result['ETag'],
                    'new_file_name' => $newFileName,
                    'original_file_name' => $originalFileName,
                    'stored_in' => 'uploads/multipart'
                ],
            ]);
        } catch (\Exception $e) {
            // General error handling
            Log::error('Error in completeMultipartUpload: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
