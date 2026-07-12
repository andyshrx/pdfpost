<?php

use App\Models\User;
use Illuminate\Support\Facades\Artisan;
use Laravel\Sanctum\Sanctum;

it('rejects unauthenticated render requests', function () {
    $this->postJson('/api/v1/render', ['html' => '<p>x</p>'])
        ->assertUnauthorized();
});

it('rejects unauthenticated template requests', function () {
    $this->getJson('/api/v1/templates')->assertUnauthorized();
});

it('rejects render requests from tokens without the render ability', function () {
    Sanctum::actingAs(User::factory()->create(), ['templates']);

    $this->postJson('/api/v1/render', ['html' => '<p>x</p>'])
        ->assertForbidden();
});

it('rejects template requests from tokens without the templates ability', function () {
    Sanctum::actingAs(User::factory()->create(), ['render']);

    $this->getJson('/api/v1/templates')->assertForbidden();
});

it('accepts a real bearer token minted by the artisan command', function () {
    User::factory()->create();

    Artisan::call('pdfpost:token', ['name' => 'ci', '--abilities' => 'templates']);
    $plainText = collect(explode("\n", trim(Artisan::output())))->last();

    $this->withHeader('Authorization', 'Bearer '.$plainText)
        ->getJson('/api/v1/templates')
        ->assertOk();
});
