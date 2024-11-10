<?php

use Illuminate\Support\Facades\Http;
use App\Models\User;

test('user can retrieve top albums', function () {
    $user = User::factory()->create();

    Http::fake([
        'http://ws.audioscrobbler.com/2.0/*' => Http::response([
            'topalbums' => [
                'album' => [
                    ['name' => 'Album A', 'artist' => ['name' => 'Artist A']],
                    ['name' => 'Album B', 'artist' => ['name' => 'Artist B']],
                ]
            ]
        ], 200)
    ]);

    $this->actingAs($user)
         ->get('/user/top-albums')
         ->assertStatus(200)
         ->assertJsonFragment(['name' => 'Album A'])
         ->assertJsonFragment(['artist' => ['name' => 'Artist A']]);
});
