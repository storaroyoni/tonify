<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\ProfileComment;
use Illuminate\Http\Request;

class ProfileCommentController extends Controller
{
    public function index(User $user)
    {
        $comments = $user->profileComments()
            ->with(['user', 'replies.user'])
            ->latest()
            ->get();

        return response()->json($comments);
    }
} 