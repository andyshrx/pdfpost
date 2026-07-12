<?php

use App\Rendering\GotenbergEngine;
use App\Rendering\RenderException;
use Illuminate\Support\Facades\Http;

it('posts html to the gotenberg chromium route and returns pdf bytes', function () {
    Http::fake(['*/forms/chromium/convert/html' => Http::response('%PDF-fake', 200)]);

    $pdf = app(GotenbergEngine::class)->render('<h1>hi</h1>');

    expect($pdf)->toBe('%PDF-fake');

    Http::assertSent(function ($request) {
        return str_ends_with($request->url(), '/forms/chromium/convert/html')
            && collect($request->data())->contains(
                fn ($part) => ($part['name'] ?? null) === 'files' && $part['contents'] === '<h1>hi</h1>'
            );
    });
});

it('maps paper size to gotenberg page dimensions', function () {
    Http::fake(['*' => Http::response('%PDF-fake', 200)]);

    app(GotenbergEngine::class)->render('<p>x</p>', ['paper_size' => 'letter']);

    Http::assertSent(fn ($request) => collect($request->data())->contains(
        fn ($part) => ($part['name'] ?? null) === 'paperWidth' && $part['contents'] === '8.5'
    ));
});

it('throws a render exception when gotenberg returns an error', function () {
    Http::fake(['*' => Http::response('chromium crashed', 500)]);

    app(GotenbergEngine::class)->render('<p>x</p>');
})->throws(RenderException::class);

it('rejects a paper size the engine does not support', function () {
    Http::fake();

    app(GotenbergEngine::class)->render('<p>x</p>', ['paper_size' => 'a3']);
})->throws(InvalidArgumentException::class);

it('renders png through the gotenberg screenshot route', function () {
    Http::fake(['*/forms/chromium/screenshot/html' => Http::response('png-bytes', 200)]);

    $png = app(GotenbergEngine::class)->render('<p>og</p>', ['format' => 'png']);

    expect($png)->toBe('png-bytes');

    Http::assertSent(function ($request) {
        return str_ends_with($request->url(), '/forms/chromium/screenshot/html')
            && collect($request->data())->contains(
                fn ($part) => ($part['name'] ?? null) === 'width' && $part['contents'] === '1200'
            );
    });
});

it('accepts custom screenshot dimensions', function () {
    Http::fake(['*' => Http::response('png-bytes', 200)]);

    app(GotenbergEngine::class)->render('<p>x</p>', ['format' => 'png', 'width' => 800, 'height' => 400]);

    Http::assertSent(fn ($request) => collect($request->data())->contains(
        fn ($part) => ($part['name'] ?? null) === 'width' && $part['contents'] === '800'
    ));
});

it('rejects an unknown format', function () {
    Http::fake();

    app(GotenbergEngine::class)->render('<p>x</p>', ['format' => 'gif']);
})->throws(InvalidArgumentException::class);
