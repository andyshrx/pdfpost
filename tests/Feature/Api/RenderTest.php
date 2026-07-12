<?php

use App\Rendering\RenderEngine;
use App\Rendering\RenderException;

it('renders inline html to a pdf', function () {
    $this->mock(RenderEngine::class)
        ->shouldReceive('render')
        ->once()
        ->with('<h1>invoice</h1>', ['paper_size' => 'letter'])
        ->andReturn('%PDF-1.7 fake pdf bytes');

    $response = $this->postJson('/api/v1/render', [
        'html' => '<h1>invoice</h1>',
        'options' => ['paper_size' => 'letter'],
    ]);

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('application/pdf');
    expect($response->getContent())->toStartWith('%PDF');
});

it('rejects a request with no html', function () {
    $this->postJson('/api/v1/render', [])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['html']);
});

it('rejects an unknown paper size', function () {
    $this->postJson('/api/v1/render', [
        'html' => '<p>x</p>',
        'options' => ['paper_size' => 'a3'],
    ])->assertUnprocessable()->assertJsonValidationErrors(['options.paper_size']);
});

it('returns 503 without leaking engine details when the render engine is down', function () {
    $this->mock(RenderEngine::class)
        ->shouldReceive('render')
        ->andThrow(new RenderException('cURL error 7: Failed to connect to localhost port 3009'));

    $response = $this->postJson('/api/v1/render', ['html' => '<p>x</p>']);

    $response->assertStatus(503)->assertJsonStructure(['message']);
    expect($response->json('message'))->not->toContain('localhost');
});

it('returns json validation errors even without an accept header', function () {
    $response = $this->post('/api/v1/render', []);

    $response->assertUnprocessable();
    expect($response->headers->get('content-type'))->toContain('application/json');
});
