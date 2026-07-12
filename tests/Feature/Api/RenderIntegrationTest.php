<?php

use App\Rendering\GotenbergEngine;

it('renders a real pdf through gotenberg', function () {
    $pdf = app(GotenbergEngine::class)->render('<h1>e2e check</h1>');

    expect(substr($pdf, 0, 4))->toBe('%PDF');
})->skip(
    fn () => env('RUN_INTEGRATION') !== '1',
    'set RUN_INTEGRATION=1 with gotenberg running on :3000'
);
