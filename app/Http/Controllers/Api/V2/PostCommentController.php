<?php

namespace App\Http\Controllers\Api\V2;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class PostCommentController extends Controller
{

    public function getCommentsByUserId(Request $request)
    {
        $userId = $request->input('user_id');

        if (!$userId) {
            return response()->json([
                'status' => 'error',
                'message' => 'User ID is required',
            ], 400);
        }

        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage = $request->input('per_page', 10);
        $status = $request->input('status');  // Додано: отримуємо параметр status

        $commentsQuery = DB::table('comments')
            ->where('user_id', $userId)
            ->orderBy($sortField, $sortOrder);

        $comments = $commentsQuery->paginate($perPage)->appends($request->except('page'));

        if ($comments->isEmpty()) {
            return response()->json([
                'status' => 'error',
                'message' => 'No comments found for this user',
                'data' => [],
            ], 404);
        }

        $commentsWithPosts = [];
        $awsUrl = env('AWS_URL');
        foreach ($comments as $comment) {
            $postQuery = DB::table('posts')
                ->where('id', $comment->post_id);

            if ($status !== null) {
                $postQuery->where('status', $status);
            }

            $post = $postQuery->first();

            if (!$post) {
                continue;
            }

            $thumbnails = DB::table('post_thumbnails')
                ->where('post_id', $post->id)
                ->get(['thumbnail', 'type']);

            foreach ($thumbnails as $thumbnail) {
                $thumbnail->thumbnail = strpos($thumbnail->thumbnail, $awsUrl) === 0 ? $thumbnail->thumbnail : $awsUrl . $thumbnail->thumbnail;
            }

            $post->thumbnails = $thumbnails;

            $post->file = strpos($post->file, $awsUrl) === 0 ? $post->file : $awsUrl . $post->file;
            $post->thumbnail = strpos($post->thumbnail, $awsUrl) === 0 ? $post->thumbnail : $awsUrl . $post->thumbnail;

            $comment->post = $post;

            $commentsWithPosts[] = $comment;
        }

        return response()->json([
            'status' => 'success',
            'message' => 'Comments fetched successfully',
            'data' => $commentsWithPosts,
            'pagination' => [
                'total' => $comments->total(),
                'per_page' => $comments->perPage(),
                'current_page' => $comments->currentPage(),
                'last_page' => $comments->lastPage(),
                'from' => $comments->firstItem(),
                'to' => $comments->lastItem(),
                'path' => $comments->path(),
                'last_page_url' => $comments->url($comments->lastPage()),
                'next_page_url' => $comments->nextPageUrl(),
            ],
        ], 200);
    }

}
