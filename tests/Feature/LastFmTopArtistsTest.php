<?php

use Illuminate\Support\Facades\Http;
use App\Models\User;
use App\Services\LastFmService;

test('user can retrieve top artists', function () {
    $user = User::factory()->create();

    Http::fake([
        'http://ws.audioscrobbler.com/2.0/*' => Http::response([
            'topartists' => [
                'artist' => [
                    ['name' => 'Artist A', 'url' => 'http://example.com/artist-a'],
                    ['name' => 'Artist B', 'url' => 'http://example.com/artist-b'],
                ]
            ]
        ], 200)
    ]);

    $this->actingAs($user)
         ->get('/user/top-artists') // will add this route in your app
         ->assertStatus(200)
         ->assertJsonFragment(['name' => 'Artist A'])
         ->assertJsonFragment(['name' => 'Artist B']);
});

