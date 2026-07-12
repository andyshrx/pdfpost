<?php

use App\Jobs\DeliverWebhook;
use App\Models\Render;
use Illuminate\Support\Facades\Http;

it('delivers a signed webhook and records the delivery', function () {
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $render = Render::factory()->succeeded()->create(['webhook_url' => 'https://example.com/hook']);

    (new DeliverWebhook($render))->handle();

    Http::assertSent(function ($request) use ($render) {
        $body = $request->body();
        $payload = json_decode($body, true);

        return $request->url() === 'https://example.com/hook'
            && $payload['render'] === $render->uuid
            && $payload['status'] === 'succeeded'
            && str_contains($payload['artifact_url'], '/artifact')
            && $request->header('X-PDFPost-Signature')[0] === hash_hmac('sha256', $body, DeliverWebhook::secret());
    });

    $delivery = $render->webhookDeliveries()->first();

    expect($delivery->response_status)->toBe(200)
        ->and($delivery->delivered_at)->not->toBeNull();
});

it('records the attempt and throws to trigger a retry on failure', function () {
    Http::fake(['example.com/*' => Http::response('nope', 500)]);

    $render = Render::factory()->succeeded()->create(['webhook_url' => 'https://example.com/hook']);

    expect(fn () => (new DeliverWebhook($render))->handle())->toThrow(RuntimeException::class);

    $delivery = $render->webhookDeliveries()->first();

    expect($delivery->response_status)->toBe(500)
        ->and($delivery->delivered_at)->toBeNull();
});

it('sends the error and no artifact url for failed renders', function () {
    Http::fake(['example.com/*' => Http::response('ok', 200)]);

    $render = Render::factory()->create([
        'status' => 'failed',
        'error' => 'engine exploded',
        'webhook_url' => 'https://example.com/hook',
    ]);

    (new DeliverWebhook($render))->handle();

    Http::assertSent(function ($request) {
        $payload = json_decode($request->body(), true);

        return $payload['status'] === 'failed'
            && $payload['artifact_url'] === null
            && $payload['error'] === 'engine exploded';
    });
});

it('uses a derived secret when none is configured', function () {
    config(['pdfpost.webhook_secret' => null]);

    expect(DeliverWebhook::secret())->toBe(hash('sha256', 'pdfpost-webhook:'.config('app.key')));

    config(['pdfpost.webhook_secret' => 'explicit']);

    expect(DeliverWebhook::secret())->toBe('explicit');
});
