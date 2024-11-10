<?php

use Illuminate\Support\Facades\Http;
use App\Models\User;

test('user can create a moodboard based on their music', function () {
    $user = User::factory()->create();

    Http::fake([
        'http://ws.audioscrobbler.com/2.0/*' => Http::response([
            'weeklytrackchart' => [
                'track' => [
                    ['name' => 'Song X', 'artist' => ['name' => 'Artist X']],
                    ['name' => 'Song Y', 'artist' => ['name' => 'Artist Y']],
                ]
            ]
        ], 200)
    ]);

    $this->actingAs($user)
         ->post('/user/moodboard')
         ->assertStatus(201)
         ->assertJsonStructure([
             'moodboard' => [
                 'songs' => [
                     '*' => ['name', 'artist' => ['name']]
                 ]
             ]
         ]);
});
