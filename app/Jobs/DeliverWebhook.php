<?php

namespace App\Jobs;

use App\Models\Render;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Str;
use RuntimeException;

class DeliverWebhook implements ShouldQueue
{
    use Queueable;

    public int $tries = 5;

    public function __construct(public Render $render) {}

    /**
     * 1m, 5m, 30m, 8h. After that the deliveries table holds the trail.
     *
     * @return array<int>
     */
    public function backoff(): array
    {
        return [60, 300, 1800, 28800];
    }

    public function handle(): void
    {
        $render = $this->render->fresh();

        if ($render === null || ! $render->webhook_url) {
            return;
        }

        $body = json_encode([
            'render' => $render->uuid,
            'status' => $render->status,
            'format' => $render->format,
            'artifact_url' => $render->status === 'succeeded' ? URL::temporarySignedRoute(
                'api.renders.artifact',
                now()->addDay(),
                ['render' => $render->uuid],
            ) : null,
            'error' => $render->error,
        ], JSON_UNESCAPED_SLASHES);

        $response = Http::timeout(10)
            ->connectTimeout(5)
            ->withHeaders([
                'Content-Type' => 'application/json',
                'X-PDFPost-Signature' => hash_hmac('sha256', $body, static::secret()),
            ])
            ->withBody($body, 'application/json')
            ->post($render->webhook_url);

        $render->webhookDeliveries()->create([
            'attempt' => $this->attempts(),
            'response_status' => $response->status(),
            'response_excerpt' => Str::limit($response->body(), 400),
            'delivered_at' => $response->successful() ? now() : null,
        ]);

        if (! $response->successful()) {
            throw new RuntimeException(
                'Webhook received HTTP '.$response->status().', will retry.'
            );
        }
    }

    /**
     * Receivers verify payloads with this shared secret. Falls back to a key
     * derived from APP_KEY so signatures work without extra setup.
     */
    public static function secret(): string
    {
        return config('pdfpost.webhook_secret') ?: hash('sha256', 'pdfpost-webhook:'.config('app.key'));
    }
}
