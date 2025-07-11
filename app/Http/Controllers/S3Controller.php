<?php

namespace App\Http\Controllers;

use Aws\S3\S3Client;
use Aws\Credentials\Credentials;
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
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'endpoint' => env('AWS_ENDPOINT', 'https://s3.eu-central-003.backblazeb2.com'),
            'use_path_style_endpoint' => true,
            'signature_version' => 'v4',
            'credentials' => new Credentials(
                env('AWS_ACCESS_KEY_ID', '0037259dc1d2ab10000000001'),
                env('AWS_SECRET_ACCESS_KEY', 'K003dHlGPBcyyZuo3A1CdmrJHbOjyMk')
            ),
            'http' => [
                'verify' => false,
            ],
        ]);

        try {
            if ($operation === 'PUT') {
                $command = $s3Client->getCommand('PutObject', [
                    'Bucket' => env('AWS_BUCKET', 'peaz-bucket'),
                    'Key' => $filePath,
                ]);
            } elseif ($operation === 'GET') {
                $command = $s3Client->getCommand('GetObject', [
                    'Bucket' => env('AWS_BUCKET', 'peaz-bucket'),
                    'Key' => $filePath,
                ]);
            } elseif ($operation === 'DELETE') {
                $command = $s3Client->getCommand('DeleteObject', [
                    'Bucket' => env('AWS_BUCKET', 'peaz-bucket'),
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
        $path = $request->path;
        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $newFileName = Str::uuid() . '.' . $extension;

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
            'region' => env('AWS_DEFAULT_REGION', 'us-east-1'),
            'endpoint' => env('AWS_ENDPOINT', 'https://s3.eu-central-003.backblazeb2.com'),
            'use_path_style_endpoint' => true,
            'signature_version' => 'v4',
            'credentials' => new Credentials(
                env('AWS_ACCESS_KEY_ID', '0037259dc1d2ab10000000001'),
                env('AWS_SECRET_ACCESS_KEY', 'K003dHlGPBcyyZuo3A1CdmrJHbOjyMk')
            ),
            'http' => [
                'verify' => false,
            ],
        ]);

        try {
            $result = $s3Client->createMultipartUpload([
                'Bucket' => env('AWS_BUCKET', 'peaz-bucket'),
                'Key' => $path . $newFileName,
                'ContentType' => $mimeType,
            ]);

            return response()->json([
                'upload_id' => $result['UploadId'],
                'file_name' => $newFileName,
                'path' => $path,
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function generateMultipartPresignedUrl(Request $request)
    {
        $fileName = $request->file_name;
        $partNumber = $request->part_number;
        $uploadId = $request->upload_id;
        $path = $request->path;

        $extension = strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
        $videoExtensions = ['mp4', 'avi', 'mov', 'mkv', 'wmv', 'flv'];

        if (!in_array($extension, $videoExtensions)) {
            return response()->json(['error' => 'Invalid file extension.'], 400);
        }

        $key = $path . $fileName;

        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => 'us-east-1',
            'endpoint' => 'https://s3.eu-central-003.backblazeb2.com',
            'use_path_style_endpoint' => true,
            'signature_version' => 'v4',
            'credentials' => new Credentials(
                env('AWS_ACCESS_KEY_ID', '0037259dc1d2ab10000000001'),
                env('AWS_SECRET_ACCESS_KEY', 'K003dHlGPBcyyZuo3A1CdmrJHbOjyMk')
            ),
            'http' => ['verify' => false],
        ]);

        try {
            $command = $s3Client->getCommand('UploadPart', [
                'Bucket' => 'peaz-bucket',
                'Key' => $key,
                'UploadId' => $uploadId,
                'PartNumber' => (int) $partNumber,
            ]);

            $presignedRequest = $s3Client->createPresignedRequest($command, '+10 minutes');

            return response()->json([
                'url' => (string) $presignedRequest->getUri(),
            ]);
        } catch (\Exception $e) {
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

    public function completeMultipartUpload(Request $request)
    {
        $originalFileName = $request->input('file_name');
        $uploadId = $request->input('upload_id');
        $parts = $request->input('parts');
        $path = $request->input('path');

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

        try {
            $result = $s3Client->completeMultipartUpload([
                'Bucket' => env('AWS_BUCKET', 'peaz-bucket'),
                'Key' => $path . $originalFileName,
                'UploadId' => $uploadId,
                'MultipartUpload' => [
                    'Parts' => $parts,
                ],
            ]);

            $uploadDir = public_path('uploads/tmp');

            if (!file_exists($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $inputFullPath = $uploadDir . '/' . $originalFileName;

            $s3Client->getObject([
                'Bucket' => env('AWS_BUCKET', 'peaz-bucket'),
                'Key' => $path . $originalFileName,
                'SaveAs' => $inputFullPath,
            ]);

            $outputExtension = pathinfo($originalFileName, PATHINFO_EXTENSION);

            if (in_array($outputExtension, $this->supportedFormats)) {
                Log::info('Dispatching ConvertVideo job', [
                    'inputFullPath' => $inputFullPath,
                    'outputExtension' => $outputExtension,
                    'outputFileName' => $originalFileName,
                ]);
                ConvertVideo::dispatch($inputFullPath, $outputExtension, $originalFileName);
                $compressionStatus = 'Compression started';
            } else {
                Log::info('Format not supported: ' . $outputExtension);
                $compressionStatus = 'Compression not supported';
            }

            return response()->json([
                'message' => 'Upload completed successfully',
                'compression_status' => $compressionStatus,
                'result' => [
                    'Location' => $s3Client->getObjectUrl(env('AWS_BUCKET'), $path . $originalFileName),
                    'Bucket' => env('AWS_BUCKET', 'peaz-bucket'),
                    'Key' => $path . $originalFileName,
                    'ETag' => $result['ETag'],
                    'file_name' => $originalFileName,
                    'stored_in' => $path,
                    'extension' => $outputExtension
                ],
            ]);
        } catch (\Exception $e) {
            Log::error('Error in completeMultipartUpload: ' . $e->getMessage());
            return response()->json(['error' => $e->getMessage()], 500);
        }
    }

}
