<?php

namespace App\Http\Controllers\Api\V2;

use App\Comment;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\CommentRating;

class CommentRatingController extends Controller
{
    public function rate(Request $request, $commentId)
    {
        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
        ]);

        $comment = Comment::find($commentId);
        if (!$comment) {
            return response()->json(['message' => 'Comment not found'], 404);
        }

        $user_id = $request->user()->id;

        $existingRating = CommentRating::where('comment_id', $commentId)
            ->where('user_id', $user_id)
            ->first();
        if ($existingRating) {
            $existingRating->update(['rating' => $request->rating]);
        } else {
            CommentRating::create([
                'comment_id' => $commentId,
                'user_id' => $user_id,
                'rating' => $request->rating,
            ]);
        }

        return response()->json(['message' => 'User with ID ' . $user_id . ' added rating ' . $request->rating . ' to comment with ID ' . $commentId], 201);
    }
}
