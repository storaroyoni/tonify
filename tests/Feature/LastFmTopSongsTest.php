<?php

use Illuminate\Support\Facades\Http;
use App\Models\User;

// creating mock user
test('user can retrieve top songs', function () {
    $user = User::factory()->create();

    // faking the Last.fm API response
    Http::fake([
        'http://ws.audioscrobbler.com/2.0/*' => Http::response([
            'toptracks' => [
                'track' => [
                    ['name' => 'Song A', 'artist' => ['name' => 'Artist A']],
                    ['name' => 'Song B', 'artist' => ['name' => 'Artist B']],
                ]
            ]
        ], 200)
    ]);

    $this->actingAs($user)
         ->get('/user/top-songs')
         ->assertStatus(200)
         ->assertJsonFragment(['name' => 'Song A'])
         ->assertJsonFragment(['artist' => ['name' => 'Artist A']]);
});
