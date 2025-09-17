<?php

use App\Models\Link;
use Illuminate\Support\Facades\URL;

it('redirects to target url when link is active', function () {
    $link = Link::factory()->create([
        'target_url' => 'https://example.com',
        'is_active' => true,
        'expires_at' => null,
        'max_clicks' => null,
    ]);

    $response = $this->get("/{$link->code}");

    $response->assertRedirect('https://example.com');

    $link->refresh();
    expect($link->click_count)->toBe(1);
});

it('returns 410 when link is expired', function () {
    $link = Link::factory()->create([
        'expires_at' => now()->subMinute(), // ya caducado
        'is_active' => true,
    ]);

    $response = $this->get("/{$link->code}");

    $response->assertStatus(410);
});

it('returns 410 when link reached max clicks', function () {
    $link = Link::factory()->create([
        'max_clicks' => 1,
        'click_count' => 1, // ya alcanzó el límite
        'is_active' => true,
    ]);

    $response = $this->get("/{$link->code}");

    $response->assertStatus(410);
});

it('returns 404 when link is inactive or not found', function () {
    $link = Link::factory()->create([
        'is_active' => false,
    ]);

    $response = $this->get("/{$link->code}");

    $response->assertNotFound();
});
