<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\FriendRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Cache;

class FriendRequestController extends Controller
{
    public function send(User $user)
    {
        $authUser = auth()->user();
        
        try {
            return DB::transaction(function () use ($user, $authUser) {
                if ($authUser->hasFriendRequestPending($user) || 
                    $authUser->hasFriendRequestReceived($user) ||
                    $authUser->isFriendsWith($user)) {
                    return back()->with('error', 'Friend request already exists or users are already friends.');
                }

                FriendRequest::create([
                    'sender_id' => $authUser->id,
                    'receiver_id' => $user->id,
                ]);

                $this->clearUserFriendshipCaches($authUser, $user);

                return back()->with('success', 'Friend request sent!');
            });
        } catch (\Exception $e) {
            \Log::error('Error sending friend request: ' . $e->getMessage());
            return back()->with('error', 'Could not send friend request. Please try again.');
        }
    }

    public function accept(FriendRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                if ($request->receiver_id !== auth()->id()) {
                    return back()->with('error', 'Unauthorized action.');
                }

                $request->update(['status' => 'accepted']);
                
                $this->clearUserFriendshipCaches(
                    User::find($request->sender_id),
                    User::find($request->receiver_id)
                );

                return back()->with('success', 'Friend request accepted!');
            });
        } catch (\Exception $e) {
            \Log::error('Error accepting friend request: ' . $e->getMessage());
            return back()->with('error', 'Could not accept friend request. Please try again.');
        }
    }

    public function reject(FriendRequest $request)
    {
        try {
            return DB::transaction(function () use ($request) {
                if ($request->receiver_id !== auth()->id()) {
                    return back()->with('error', 'Unauthorized action.');
                }

                $request->update(['status' => 'rejected']);
                    $this->clearUserFriendshipCaches(
                    User::find($request->sender_id),
                    User::find($request->receiver_id)
                );

                return back()->with('success', 'Friend request rejected!');
            });
        } catch (\Exception $e) {
            \Log::error('Error rejecting friend request: ' . $e->getMessage());
            return back()->with('error', 'Could not reject friend request. Please try again.');
        }
    }

    public function remove(User $user)
    {
        try {
            return DB::transaction(function () use ($user) {
                $authUser = auth()->user();
                
                if (!$authUser->isFriendsWith($user)) {
                    return back()->with('error', 'User is not your friend');
                }

                FriendRequest::where(function ($query) use ($user, $authUser) {
                    $query->where('sender_id', $authUser->id)
                          ->where('receiver_id', $user->id)
                          ->where('status', 'accepted');
                })->orWhere(function ($query) use ($user, $authUser) {
                    $query->where('sender_id', $user->id)
                          ->where('receiver_id', $authUser->id)
                          ->where('status', 'accepted');
                })->delete();

                $this->clearUserFriendshipCaches($authUser, $user);

                return back()->with('success', 'Friend removed successfully');
            });
        } catch (\Exception $e) {
            \Log::error('Error removing friend: ' . $e->getMessage());
            return back()->with('error', 'Could not remove friend. Please try again.');
        }
    }

    private function clearUserFriendshipCaches(User $user1, User $user2)
    {
        Cache::forget("user_friends_{$user1->id}");
        Cache::forget("user_friends_{$user2->id}");
        Cache::forget("friend_requests_{$user1->id}");
        Cache::forget("friend_requests_{$user2->id}");
    }
} 