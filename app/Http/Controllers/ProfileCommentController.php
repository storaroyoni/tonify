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
            return back()->with('error', 'Only friends can comment on profiles');
        }

        $validated = $request->validate([
            'content' => 'required|string|max:500'
        ]);

        ProfileComment::create([
            'content' => $validated['content'],
            'user_id' => auth()->id(),
            'profile_user_id' => $user->id
        ]);

        return back()->with('success', 'Comment posted successfully!');
    }

    public function destroy(ProfileComment $comment)
    {
        if (auth()->id() === $comment->user_id || 
            auth()->id() === $comment->profile_user_id) {
            $comment->delete();
            return back()->with('success', 'Comment deleted successfully!');
        }

        return back()->with('error', 'Unauthorized action');
    }
} 