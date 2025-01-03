<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\ProfileComment;
use Illuminate\Http\Request;

class ProfileCommentController extends Controller
{
    public function store(Request $request, User $user)
    {
        if (!auth()->user()->isFriendsWith($user) && auth()->id() !== $user->id) {
            return response()->json(['error' => 'Only friends can comment on profiles'], 403);
        }

        $validated = $request->validate([
            'content' => 'required|string|max:500'
        ]);

        $comment = ProfileComment::create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
            'profile_user_id' => $user->id
        ]);

        $comment->load('user');

        return response()->json($comment);
    }

    public function destroy(ProfileComment $comment)
    {
        if (auth()->id() === $comment->user_id || 
            auth()->id() === $comment->profile_user_id) {
            $comment->delete();
            return response()->json(['message' => 'Comment deleted successfully']);
        }

        return response()->json(['error' => 'Unauthorized action'], 403);
    }
} 