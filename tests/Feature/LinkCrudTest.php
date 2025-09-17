<?php

use App\Models\User;
use App\Models\Link;
use Illuminate\Support\Str;

it('allows an authenticated user to create a short link', function () {
    $user = User::factory()->create();

    $this->actingAs($user);

    $response = $this->post('/links', [
        'target_url' => 'https://laravel.com',
        'max_clicks' => 5,
    ]);

    $response->assertRedirect();
    $this->assertDatabaseHas('links', [
        'target_url' => 'https://laravel.com',
        'user_id' => $user->id,
    ]);
});
