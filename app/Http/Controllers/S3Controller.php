<?php

namespace App\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;

class S3Controller extends Controller
{

    /**
     * Generate a presigned URL for an S3 operation.
     *
     * This function creates a presigned URL for performing operations such as PUT, GET, or DELETE
     * on files stored in AWS S3. It takes into account the folder type (e.g., 'users', 'posts'),
     * validates file extensions based on the folder type, and constructs the appropriate S3 command.
     *
     * @param Request $request The HTTP request containing the operation type, folder, and file name.
     *                         - 'operation': The S3 operation to perform ('PUT', 'GET', 'DELETE').
     *                         - 'folder': The folder type ('users', 'posts', or 'posts/thumbnails').
     *                         - 'file_name': The name of the file to operate on.
     * @return \Illuminate\Http\JsonResponse A JSON response containing the presigned URL or an error message.
     */
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

    public function initiateMultipartUpload(Request $request)
    {
        $fileName = $request->file_name; // e.g., 'large_video.mp4'

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
