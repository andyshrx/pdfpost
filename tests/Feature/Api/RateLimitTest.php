<?php

use App\Models\User;
use Laravel\Sanctum\Sanctum;

it('rate limits api requests per user', function () {
    config(['pdfpost.rate_limit' => 2]);

    Sanctum::actingAs(User::factory()->create(), ['templates']);

    $this->getJson('/api/v1/templates')->assertOk();
    $this->getJson('/api/v1/templates')->assertOk();
    $this->getJson('/api/v1/templates')->assertStatus(429);
});

it('reports the limit in response headers', function () {
    config(['pdfpost.rate_limit' => 5]);

    Sanctum::actingAs(User::factory()->create(), ['templates']);

    $this->getJson('/api/v1/templates')
        ->assertOk()
        ->assertHeader('X-RateLimit-Limit', 5);
});
