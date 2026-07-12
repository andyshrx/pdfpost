<?php

use App\Jobs\DeliverWebhook;
use App\Jobs\ProcessRender;
use App\Models\Render;
use App\Models\Template;
use App\Models\User;
use App\Rendering\LiquidRenderer;
use App\Rendering\RenderEngine;
use App\Rendering\RenderException;
use Illuminate\Support\Facades\Queue;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\URL;
use Laravel\Sanctum\Sanctum;

beforeEach(function () {
    Sanctum::actingAs(User::factory()->create(), ['render', 'templates']);
});

it('queues an async render and returns 202', function () {
    Queue::fake();

    $response = $this->postJson('/api/v1/renders', [
        'html' => '<h1>async</h1>',
        'webhook_url' => 'https://example.com/hook',
    ]);

    $response->assertStatus(202)->assertJsonPath('data.status', 'queued');

    Queue::assertPushed(ProcessRender::class);
    expect(Render::first()->webhook_url)->toBe('https://example.com/hook');
});

it('processes a queued render and stores the artifact', function () {
    Storage::fake('local');

    $this->mock(RenderEngine::class)
        ->shouldReceive('render')
        ->once()
        ->andReturn('%PDF-async');

    $render = Render::factory()->create();

    (new ProcessRender($render))->handle(app(RenderEngine::class), app(LiquidRenderer::class));

    $render->refresh();

    expect($render->status)->toBe('succeeded')
        ->and($render->artifact_path)->toBe('renders/'.$render->uuid.'.pdf')
        ->and($render->duration_ms)->not->toBeNull();

    Storage::disk('local')->assertExists($render->artifact_path);
});

it('renders template based async jobs through liquid', function () {
    Storage::fake('local');

    $template = Template::factory()->create(['slug' => 'invoice']);
    $version = $template->publishNewVersion('<p>{{ total }}</p>');

    $this->mock(RenderEngine::class)
        ->shouldReceive('render')
        ->once()
        ->withArgs(fn ($html) => $html === '<p>$5.00</p>')
        ->andReturn('%PDF-tpl');

    $render = Render::factory()->create([
        'html' => null,
        'template_version_id' => $version->id,
        'payload' => ['total' => '$5.00'],
    ]);

    (new ProcessRender($render))->handle(app(RenderEngine::class), app(LiquidRenderer::class));

    expect($render->fresh()->status)->toBe('succeeded');
});

it('marks a render failed and queues the webhook when the job gives up', function () {
    Queue::fake();

    $render = Render::factory()->create(['webhook_url' => 'https://example.com/hook']);

    (new ProcessRender($render))->failed(new RenderException('engine exploded'));

    expect($render->fresh()->status)->toBe('failed')
        ->and($render->fresh()->error)->toContain('engine exploded');

    Queue::assertPushed(DeliverWebhook::class);
});

it('reports render status with a signed artifact url once succeeded', function () {
    $render = Render::factory()->succeeded()->create();

    $this->getJson('/api/v1/renders/'.$render->uuid)
        ->assertOk()
        ->assertJsonPath('data.status', 'succeeded')
        ->assertJsonPath('data.id', $render->uuid);

    $url = $this->getJson('/api/v1/renders/'.$render->uuid)->json('data.artifact_url');

    expect($url)->toContain('/api/v1/renders/'.$render->uuid.'/artifact')
        ->and($url)->toContain('signature=');
});

it('serves artifacts through valid signed urls only', function () {
    Storage::fake('local');
    Storage::disk('local')->put('renders/test.pdf', '%PDF-artifact');

    $render = Render::factory()->succeeded()->create();

    // unsigned request is refused
    $this->get('/api/v1/renders/'.$render->uuid.'/artifact')->assertForbidden();

    $signed = URL::temporarySignedRoute('api.renders.artifact', now()->addMinutes(5), ['render' => $render->uuid]);

    $this->get($signed)
        ->assertOk()
        ->assertHeader('content-type', 'application/pdf');
});

it('requires the render ability for async renders', function () {
    Sanctum::actingAs(User::factory()->create(), ['templates']);

    $this->postJson('/api/v1/renders', ['html' => '<p>x</p>'])->assertForbidden();
});

it('renders png synchronously with the right content type', function () {
    $this->mock(RenderEngine::class)
        ->shouldReceive('render')
        ->once()
        ->withArgs(fn ($html, $options) => ($options['format'] ?? null) === 'png')
        ->andReturn('png-bytes');

    $response = $this->postJson('/api/v1/render', [
        'html' => '<h1>og image</h1>',
        'format' => 'png',
    ]);

    $response->assertOk();
    expect($response->headers->get('content-type'))->toContain('image/png');
});
