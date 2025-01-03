<?php

namespace App\Http\Controllers;

use App\Models\ProfileComment;
use App\Models\CommentReply;
use Illuminate\Http\Request;

class CommentReplyController extends Controller
{
    public function store(Request $request, ProfileComment $comment)
    {
        if (!auth()->user()->isFriendsWith($comment->profileUser) && auth()->id() !== $comment->profile_user_id) {
            return back()->with('error', 'Only friends and the profile owner can reply to comments');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:500'
        ]);

        CommentReply::create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
            'comment_id' => $comment->id
        ]);

        return back()->with('success', 'Reply posted successfully!');
    }

    public function destroy(CommentReply $reply)
    {
        if (auth()->id() === $reply->user_id || 
            auth()->id() === $reply->comment->profileUser->id) {
            $reply->delete();
            return back()->with('success', 'Reply deleted successfully!');
        }

        return back()->with('error', 'Unauthorized action');
    }
} 