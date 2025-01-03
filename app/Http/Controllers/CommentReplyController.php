<?php

namespace App\Http\Controllers;

use App\Models\ProfileComment;
use App\Models\CommentReply;
use Illuminate\Http\Request;

class CommentReplyController extends Controller
{
    public function store(Request $request, ProfileComment $comment)
    {
        if (!auth()->user()->isFriendsWith($comment->profileUser) && 
            auth()->id() !== $comment->profile_user_id) {
            return response()->json(['error' => 'Only friends can reply to comments'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:500'
        ]);

        $reply = CommentReply::create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
            'comment_id' => $comment->id
        ]);

        $reply->load('user');

        return response()->json($reply);
    }

    public function destroy(CommentReply $reply)
    {
        if (auth()->id() === $reply->user_id || 
            auth()->id() === $reply->comment->profile_user_id) {
            $reply->delete();
            return response()->json(['message' => 'Reply deleted successfully']);
        }

        return response()->json(['error' => 'Unauthorized action'], 403);
    }
} 