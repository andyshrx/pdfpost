<?php

use App\Models\Template;
use App\Models\User;

it('walks through account, seed and token on a fresh install', function () {
    $this->artisan('pdfpost:install')
        ->expectsQuestion('Your name', 'Andy')
        ->expectsQuestion('Email address (you log in with this)', 'Andy@example.com')
        ->expectsQuestion('Password', 'correct-horse')
        ->expectsConfirmation('Seed the sample template gallery?', 'yes')
        ->expectsConfirmation('Mint an API token now?', 'yes')
        ->expectsQuestion('A label for the token, e.g. the app that will use it', 'my-app')
        ->expectsOutputToContain('Save this token now')
        ->assertSuccessful();

    $user = User::sole();

    expect($user->email)->toBe('andy@example.com')
        ->and($user->tokens()->count())->toBe(1)
        ->and($user->tokens()->first()->abilities)->toBe(['render', 'templates'])
        ->and(Template::count())->toBeGreaterThan(0);
});

it('can skip the seed and the token', function () {
    $this->artisan('pdfpost:install')
        ->expectsQuestion('Your name', 'Andy')
        ->expectsQuestion('Email address (you log in with this)', 'andy@example.com')
        ->expectsQuestion('Password', 'correct-horse')
        ->expectsConfirmation('Seed the sample template gallery?', 'no')
        ->expectsConfirmation('Mint an API token now?', 'no')
        ->assertSuccessful();

    expect(User::sole()->tokens()->count())->toBe(0)
        ->and(Template::count())->toBe(0);
});

it('bails out when an account already exists', function () {
    User::factory()->create();

    $this->artisan('pdfpost:install')
        ->expectsOutputToContain('already set up')
        ->assertSuccessful();

    expect(User::count())->toBe(1);
});
