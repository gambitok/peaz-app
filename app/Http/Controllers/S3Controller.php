<?php

namespace App\Http\Controllers;

use Aws\S3\S3Client;
use Illuminate\Http\Request;

class S3Controller extends Controller
{

    public function generatePresignedUrl(Request $request)
    {
        // Get the operation (PUT, GET, DELETE, etc.)
        $operation = strtoupper($request->operation); // e.g., 'PUT', 'GET', 'DELETE'

        // Get the file name from the request
        $fileName = 'uploads/' . $request->file_name;

        // Set up the AWS S3 client
        $s3Client = new S3Client([
            'version' => 'latest',
            'region' => env('AWS_DEFAULT_REGION'),
            'credentials' => [
                'key'    => env('AWS_ACCESS_KEY_ID'),
                'secret' => env('AWS_SECRET_ACCESS_KEY'),
            ],
        ]);

        // Select the appropriate command based on the operation
        $command = null;
        if ($operation === 'PUT') {
            $command = $s3Client->getCommand('PutObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $fileName,
            ]);
        } elseif ($operation === 'GET') {
            $command = $s3Client->getCommand('GetObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $fileName,
            ]);
        } elseif ($operation === 'DELETE') {
            $command = $s3Client->getCommand('DeleteObject', [
                'Bucket' => env('AWS_BUCKET'),
                'Key' => $fileName,
            ]);
        } else {
            return response()->json(['error' => 'Invalid operation type.'], 400);
        }

        // Generate the presigned URL
        $presignedRequest = $s3Client->createPresignedRequest($command, '+10 minutes');

        // Get the presigned URL as a string
        $presignedUrl = (string) $presignedRequest->getUri();

        // Return the presigned URL
        return response()->json(['url' => $presignedUrl]);
    }
}
