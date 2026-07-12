<?php

use App\Models\User;

it('fails when no users exist', function () {
    $this->artisan('pdfpost:token', ['name' => 'ci'])
        ->expectsOutputToContain('No users exist yet')
        ->assertFailed();
});

it('mints a token with the requested abilities for the first user', function () {
    $user = User::factory()->create();

    $this->artisan('pdfpost:token', ['name' => 'ci', '--abilities' => 'render'])
        ->expectsOutputToContain('render')
        ->assertSuccessful();

    $token = $user->tokens()->first();

    expect($token->name)->toBe('ci')
        ->and($token->abilities)->toBe(['render']);
});
