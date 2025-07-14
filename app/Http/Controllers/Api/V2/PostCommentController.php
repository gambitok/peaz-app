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
        $status = $request->input('status');

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
        $awsBucket = env('AWS_BUCKET');

        $currentUserId = $request->user()?->id;

        foreach ($comments as $comment) {
            $postQuery = DB::table('posts')->where('id', $comment->post_id);

            if ($status !== null) {
                $postQuery->where('status', $status);
            }

            $post = $postQuery->first();

            if (!$post) {
                continue;
            }

            $commentlikeCount = DB::table('commentlikes')
                ->where('comment_id', $comment->id)
                ->count();

            $isLiked = false;
            if ($currentUserId) {
                $isLiked = DB::table('commentlikes')
                    ->where('comment_id', $comment->id)
                    ->where('user_id', $currentUserId)
                    ->exists();
            }

            $comment->commentlike_count = $commentlikeCount;
            $comment->is_commentlike = $isLiked ? 1 : 0;

            $user = DB::table('users')
                ->select('id', 'name', 'username', 'profile_image', 'bio', 'website', 'verified')
                ->where('id', $comment->user_id)
                ->first();

            $comment->user = $user;
            unset($comment->user_id);

            $thumbnails = DB::table('post_thumbnails')
                ->where('post_id', $post->id)
                ->get(['thumbnail', 'type']);

            foreach ($thumbnails as $thumbnail) {
                $thumbnail->thumbnail = strpos($thumbnail->thumbnail, $awsUrl) === 0
                    ? $thumbnail->thumbnail
                    : $awsUrl . '/' . $awsBucket . $thumbnail->thumbnail;
            }

            $post->thumbnails = $thumbnails;
            $post->file = strpos($post->file, $awsUrl) === 0 ? $post->file : $awsUrl . '/' . $awsBucket . $post->file;
            $post->thumbnail = strpos($post->thumbnail, $awsUrl) === 0 ? $post->thumbnail : $awsUrl . '/' . $awsBucket . $post->thumbnail;

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

    public function getCommentsByPostId(Request $request)
    {
        $postId = $request->input('post_id');

        if (!$postId) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Post ID is required',
            ], 400);
        }

        $sortField = $request->input('sort_by', 'created_at');
        $sortOrder = $request->input('sort_order', 'desc');
        $perPage   = $request->input('per_page', 10);
        $status    = $request->input('status');
        $hasRating = $request->input('has_rating'); // new filter

        $commentsQuery = DB::table('comments')
            ->where('post_id', $postId)
            ->orderBy($sortField, $sortOrder);

        // 🔍 apply filter for rating presence
        if ($hasRating !== null) {
            if ((int)$hasRating === 1) {
                $commentsQuery->whereNotNull('rating');
            } elseif ((int)$hasRating === 0) {
                $commentsQuery->whereNull('rating');
            }
        }

        $comments = $commentsQuery
            ->paginate($perPage)
            ->appends($request->except('page'));

        if ($comments->isEmpty()) {
            return response()->json([
                'status'  => 'error',
                'message' => 'No comments found for this post',
                'data'    => [],
            ], 404);
        }

        $postQuery = DB::table('posts')->where('id', $postId);

        if ($status !== null) {
            $postQuery->where('status', $status);
        }

        $post = $postQuery->first();

        if (!$post) {
            return response()->json([
                'status'  => 'error',
                'message' => 'Post not found or status mismatch',
            ], 404);
        }

        $awsUrl    = env('AWS_URL');
        $awsBucket = env('AWS_BUCKET');

        $thumbnails = DB::table('post_thumbnails')
            ->where('post_id', $post->id)
            ->get(['thumbnail', 'type']);

        foreach ($thumbnails as $thumb) {
            $thumb->thumbnail = str_starts_with($thumb->thumbnail, $awsUrl)
                ? $thumb->thumbnail
                : $awsUrl . '/' . $awsBucket . $thumb->thumbnail;
        }

        $post->thumbnails = $thumbnails;

        $post->file = str_starts_with($post->file, $awsUrl)
            ? $post->file
            : $awsUrl . '/' . $awsBucket . $post->file;

        $post->thumbnail = str_starts_with($post->thumbnail, $awsUrl)
            ? $post->thumbnail
            : $awsUrl . '/' . $awsBucket . $post->thumbnail;

        $currentUserId = $request->user()?->id;

        foreach ($comments as $comment) {
            $commentlikeCount = DB::table('commentlikes')
                ->where('comment_id', $comment->id)
                ->count();

            $isLiked = false;
            if ($currentUserId) {
                $isLiked = DB::table('commentlikes')
                    ->where('comment_id', $comment->id)
                    ->where('user_id', $currentUserId)
                    ->exists();
            }

            $comment->commentlike_count = $commentlikeCount;
            $comment->is_commentlike = $isLiked ? 1 : 0;

            $user = DB::table('users')
                ->select('id', 'name', 'username', 'profile_image', 'bio', 'website', 'verified')
                ->where('id', $comment->user_id)
                ->first();

            if ($user) {
                $awsUrl = env('AWS_URL'); // https://s3.eu-central-003.backblazeb2.com
                $awsBucket = env('AWS_BUCKET'); // peaz-bucket

                if (!str_starts_with($user->profile_image, $awsUrl)) {
                    $user->profile_image = $awsUrl . '/' . $awsBucket . '/' . ltrim($user->profile_image, '/');
                }
            }

            $comment->user = $user;
            unset($comment->user_id);

            $comment->post = $post;
        }

        return response()->json([
            'status'     => 'success',
            'message'    => 'Comments fetched successfully',
            'data'       => $comments->items(),
            'pagination' => [
                'total'         => $comments->total(),
                'per_page'      => $comments->perPage(),
                'current_page'  => $comments->currentPage(),
                'last_page'     => $comments->lastPage(),
                'from'          => $comments->firstItem(),
                'to'            => $comments->lastItem(),
                'path'          => $comments->path(),
                'last_page_url' => $comments->url($comments->lastPage()),
                'next_page_url' => $comments->nextPageUrl(),
            ],
        ], 200);
    }


}
