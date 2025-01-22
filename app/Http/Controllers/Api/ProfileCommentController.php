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
        $comments = ProfileComment::with('user')
            ->where('profile_user_id', $user->id)
            ->latest()
            ->get();
            
        return response()->json($comments);
    }
} 