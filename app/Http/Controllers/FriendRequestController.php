<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FriendRequest;
use Illuminate\Http\Request;

class FriendRequestController extends Controller
{
    public function send(User $user)
    {
        if (auth()->user()->hasFriendRequestPending($user) || 
            auth()->user()->hasFriendRequestReceived($user) ||
            auth()->user()->isFriendsWith($user)) {
            return back()->with('error', 'Friend request already exists or users are already friends.');
        }

        FriendRequest::create([
            'sender_id' => auth()->id(),
            'receiver_id' => $user->id,
        ]);

        return back()->with('success', 'Friend request sent!');
    }

    public function accept(FriendRequest $request)
    {
        if ($request->receiver_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->update(['status' => 'accepted']);

        return back()->with('success', 'Friend request accepted!');
    }

    public function reject(FriendRequest $request)
    {
        if ($request->receiver_id !== auth()->id()) {
            return back()->with('error', 'Unauthorized action.');
        }

        $request->update(['status' => 'rejected']);

        return back()->with('success', 'Friend request rejected!');
    }

    public function remove(User $user)
    {
        if (auth()->user()->isFriendsWith($user)) {
            FriendRequest::where(function ($query) use ($user) {
                $query->where('sender_id', auth()->id())
                      ->where('receiver_id', $user->id)
                      ->where('status', 'accepted');
            })->orWhere(function ($query) use ($user) {
                $query->where('sender_id', $user->id)
                      ->where('receiver_id', auth()->id())
                      ->where('status', 'accepted');
            })->delete();

            return back()->with('success', 'Friend removed successfully');
        }

        return back()->with('error', 'User is not your friend');
    }
} 