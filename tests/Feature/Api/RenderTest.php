<?php

use App\Models\Template;
use App\Models\User;
use App\Rendering\RenderEngine;
use App\Rendering\RenderException;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create(), ['render', 'templates']);
});

it('renders inline html to a pdf', function () {
    $this->mock(RenderEngine::class)
        ->shouldReceive('render')
        ->once()
        ->with('<h1>invoice</h1>', ['paper_size' => 'letter', 'format' => 'pdf'])
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

it('renders a saved template with data merged in', function () {
    $template = Template::factory()->create(['slug' => 'invoice']);
    $template->publishNewVersion('<h1>Invoice for {{ customer }}</h1>');

    $this->mock(RenderEngine::class)
        ->shouldReceive('render')
        ->once()
        ->with('<h1>Invoice for Acme Co</h1>', ['format' => 'pdf'])
        ->andReturn('%PDF-1.7 merged');

    $this->postJson('/api/v1/render', [
        'template' => 'invoice',
        'data' => ['customer' => 'Acme Co'],
    ])->assertOk();
});

it('rejects a render for an unknown template', function () {
    $this->postJson('/api/v1/render', ['template' => 'missing'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['template']);
});

it('rejects a render when both html and template are given', function () {
    Template::factory()->create(['slug' => 'invoice'])->publishNewVersion('x');

    $this->postJson('/api/v1/render', [
        'html' => '<p>x</p>',
        'template' => 'invoice',
    ])->assertUnprocessable();
});

it('rejects a render for a template with no published version', function () {
    Template::factory()->create(['slug' => 'empty']);

    $this->postJson('/api/v1/render', ['template' => 'empty'])
        ->assertUnprocessable()
        ->assertJsonValidationErrors(['template']);
});
