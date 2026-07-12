<?php

namespace App\Jobs;

use App\Models\Render;
use App\Models\User;
use App\Notifications\RenderFailuresDetected;
use App\Rendering\LiquidRenderer;
use App\Rendering\RenderEngine;
use App\Rendering\TemplateSyntaxException;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Queue\Queueable;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use Throwable;

class ProcessRender implements ShouldQueue
{
    use Queueable;

    /**
     * Engine hiccups (restarting container, transient 5xx) deserve retries.
     */
    public int $tries = 3;

    /** @var array<int> */
    public array $backoff = [10, 60];

    public function __construct(public Render $render) {}

    public function handle(RenderEngine $engine, LiquidRenderer $liquid): void
    {
        $render = $this->render->fresh();

        if ($render === null || $render->status === 'succeeded') {
            return;
        }

        $render->update(['status' => 'processing']);

        $startedAt = hrtime(true);

        try {
            $html = $render->html ?? $liquid->render(
                $render->templateVersion->liquid_source,
                $render->payload ?? [],
            );

            $bytes = $engine->render($html, array_merge(
                $render->options ?? [],
                ['format' => $render->format],
            ));
        } catch (TemplateSyntaxException $e) {
            // broken liquid will not fix itself on retry
            $this->fail($e);

            return;
        }

        $disk = config('pdfpost.artifact_disk');
        $path = 'renders/'.$render->uuid.'.'.$render->fileExtension();

        Storage::disk($disk)->put($path, $bytes);

        $render->markSucceeded($disk, $path, (int) ((hrtime(true) - $startedAt) / 1_000_000));

        $this->notify($render);
    }

    public function failed(?Throwable $exception): void
    {
        $render = $this->render->fresh();

        if ($render === null) {
            return;
        }

        $render->markFailed($exception?->getMessage() ?? 'Render failed.');

        $this->notify($render);
        $this->alertOperatorIfFailingRepeatedly($render);
    }

    /**
     * Three failures in a row usually means the engine is down, not a bad
     * template. Tell the operator, at most once an hour.
     */
    protected function alertOperatorIfFailingRepeatedly(Render $render): void
    {
        $recent = Render::whereNotNull('completed_at')
            ->latest('completed_at')
            ->take(3)
            ->pluck('status');

        if ($recent->count() < 3 || ! $recent->every(fn ($status) => $status === 'failed')) {
            return;
        }

        if (! Cache::add('pdfpost:failure-alerted', true, now()->addHour())) {
            return;
        }

        User::query()->oldest('id')->first()?->notify(new RenderFailuresDetected($render->error));
    }

    protected function notify(Render $render): void
    {
        if ($render->webhook_url) {
            DeliverWebhook::dispatch($render);
        }
    }
}
