<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SearchController extends Controller
{
    public function search(Request $request)
    {
        $query = $request->input('query');
        
        if (strlen($query) >= 2) {
            $users = User::where('name', 'ilike', "%{$query}%")
                        ->where('id', '!=', auth()->id()) // excluding current user
                        ->select('id', 'name', 'profile_picture')
                        ->groupBy('id', 'name', 'profile_picture')
                        ->limit(5)
                        ->get();

            return response()->json([
                'users' => $users->map(function($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'profile_picture' => $user->profile_picture 
                            ? Storage::url($user->profile_picture) 
                            : null,
                        'url' => "/profile/{$user->name}"
                    ];
                })
            ]);
        }

        return response()->json(['users' => []]);
    }
} 